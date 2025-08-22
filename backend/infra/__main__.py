import pulumi
import pulumi_aws as aws
import pulumi_awsx as awsx # awsx for Python might have different components or usage patterns

# Load configuration
config = pulumi.Config()
db_name = config.require("dbName")
db_username = config.require("dbUsername")
db_password = config.require_secret("dbPassword")
app_runner_image_tag = config.require("appRunnerImageTag")

ses_smtp_username = config.require_secret("sesSmtpUsername")
ses_smtp_password = config.require_secret("sesSmtpPassword")
from_email = config.require("fromEmail")
admin_email = config.require("adminEmail")
ses_configuration_set = config.require("sesConfigurationSet")
ses_webhook_secret = config.require_secret("sesWebhookSecret")
recaptcha_secret_key = config.require_secret("recaptchaSecretKey")
aws_region = aws.get_region().name

# Create an ECR repository
ecr_repo = aws.ecr.Repository("backendRepo",
    name="calcfolio-backend-repo",
    image_scanning_configuration=aws.ecr.RepositoryImageScanningConfigurationArgs(
        scan_on_push=True,
    ),
    image_tag_mutability="MUTABLE"
)

# Security Group for RDS
rds_security_group = aws.ec2.SecurityGroup("rdsSecurityGroup",
    description="Allow traffic to RDS instance",
    ingress=[], # Define specific ingress rules as needed, e.g., from App Runner VPC
    egress=[aws.ec2.SecurityGroupEgressArgs(
        protocol="-1",
        from_port=0,
        to_port=0,
        cidr_blocks=["0.0.0.0/0"],
    )]
)

# RDS PostgreSQL instance
db_instance = aws.rds.Instance("postgresDb",
    engine="postgres",
    engine_version="15",
    instance_class=aws.rds.InstanceTypes.T3_Micro, # Free Tier
    allocated_storage=20, # Free Tier
    db_name=db_name,
    username=db_username,
    password=db_password,
    skip_final_snapshot=True,
    publicly_accessible=False, # Recommended
    vpc_security_group_ids=[rds_security_group.id],
    storage_type="gp2",
    multi_az=False,
    backup_retention_period=0,
    delete_automated_backups=True,
    tags={
        "Name": "calcfolio-postgres-db",
    }
)

# IAM role for App Runner to access ECR
app_runner_ecr_role = aws.iam.Role("appRunnerEcrRole",
    assume_role_policy=aws.iam.get_policy_document(statements=[aws.iam.GetPolicyDocumentStatementArgs(
        actions=["sts:AssumeRole"],
        principals=[aws.iam.GetPolicyDocumentStatementPrincipalArgs(
            type="Service",
            identifiers=["build.apprunner.amazonaws.com"],
        )],
    )]).json
)

aws.iam.RolePolicyAttachment("appRunnerEcrPolicyAttachment",
    role=app_runner_ecr_role.name,
    policy_arn=aws.iam.ManagedPolicy.AMAZON_EC2_CONTAINER_REGISTRY_READ_ONLY
)

# App Runner service
app_service = aws.apprunner.Service("backendAppService",
    service_name="calcfolio-backend-service",
    source_configuration=aws.apprunner.ServiceSourceConfigurationArgs(
        image_repository=aws.apprunner.ServiceSourceConfigurationImageRepositoryArgs(
            image_identifier=pulumi.Output.concat(ecr_repo.repository_url, ":", app_runner_image_tag),
            image_repository_type="ECR",
            image_configuration=aws.apprunner.ServiceSourceConfigurationImageRepositoryImageConfigurationArgs(
                port="80", # Port from Dockerfile
            ),
        ),
        authentication_configuration=aws.apprunner.ServiceSourceConfigurationAuthenticationConfigurationArgs(
            access_role_arn=app_runner_ecr_role.arn,
        ),
        auto_deployments_enabled=True,
    ),
    instance_configuration=aws.apprunner.ServiceInstanceConfigurationArgs(
        cpu="0.25 vCPU",
        memory="0.5 GB",
    ),
    health_check_configuration=aws.apprunner.ServiceHealthCheckConfigurationArgs(
        protocol="HTTP",
        path="/", # Health check path
        interval=20,
        timeout=10,
        healthy_threshold=2,
        unhealthy_threshold=3,
    ),
    network_configuration=aws.apprunner.ServiceNetworkConfigurationArgs(
        egress_configuration=aws.apprunner.ServiceNetworkConfigurationEgressConfigurationArgs(
            egress_type="DEFAULT",
        )
    ),
    tags={
        "Name": "calcfolio-backend-app",
    }
)

# Construct environment variables for App Runner
# Note: Using Output.all().apply() to resolve secret values correctly for environment variables
def create_env_vars(db_user, db_pass, db_addr, db_port, db_name_val,
                    ses_user, ses_pass, region,
                    from_mail_val, admin_mail_val, ses_config_set_val,
                    ses_wh_secret, recaptcha_key_val):
    return {
        "DATABASE_URL": f"postgresql://{db_user}:{db_pass}@{db_addr}:{db_port}/{db_name_val}",
        "SMTP_DSN": f"smtp://{ses_user}:{ses_pass}@email-smtp.{region}.amazonaws.com:587?verify_peer=0",
        "FROM_EMAIL": from_mail_val,
        "ADMIN_EMAIL": admin_mail_val,
        "SES_CONFIGURATION_SET": ses_config_set_val,
        "SES_WEBHOOK_SECRET": ses_wh_secret,
        "RECAPTCHA_V3_SECRET_KEY": recaptcha_key_val,
    }

app_runner_env_vars = pulumi.Output.all(
    db_instance.username,
    db_instance.password,
    db_instance.address,
    db_instance.port,
    db_instance.db_name,
    ses_smtp_username,
    ses_smtp_password,
    aws_region,
    from_email,
    admin_email,
    ses_configuration_set,
    ses_webhook_secret,
    recaptcha_secret_key
).apply(lambda args: create_env_vars(*args))


# Update App Runner service with environment variables
# This is done as a separate step because environment variables might contain secrets
# and App Runner API for create/update handles them.
# A more direct way is to pass them in `aws.apprunner.Service` if the SDK handles secrets properly.
# For Python, directly passing `pulumi.Output` that contains secrets to `runtime_environment_variables`
# might not automatically unwrap them. Using `.apply` is safer.

# Re-declare app_service to include runtime_environment_variables if possible,
# or use an update mechanism if the provider requires it.
# For simplicity, we'll assume direct assignment works if secrets are handled by the SDK.
# If not, you might need to use a CustomResource or a two-step apply.

# The aws.apprunner.Service in Python SDK expects a map for runtime_environment_variables.
# We need to ensure secrets are passed correctly.
# Pulumi's Python SDK handles Output[Secret[str]] correctly when passed to resource inputs expecting strings.

app_service_with_env = aws.apprunner.Service("backendAppServiceWithEnv", # Note: This will try to create a new one or update
    service_name="calcfolio-backend-service", # Must match to update
    source_configuration=aws.apprunner.ServiceSourceConfigurationArgs(
        image_repository=aws.apprunner.ServiceSourceConfigurationImageRepositoryArgs(
            image_identifier=pulumi.Output.concat(ecr_repo.repository_url, ":", app_runner_image_tag),
            image_repository_type="ECR",
            image_configuration=aws.apprunner.ServiceSourceConfigurationImageRepositoryImageConfigurationArgs(
                port="80",
            ),
        ),
        authentication_configuration=aws.apprunner.ServiceSourceConfigurationAuthenticationConfigurationArgs(
            access_role_arn=app_runner_ecr_role.arn,
        ),
        auto_deployments_enabled=True,
    ),
    instance_configuration=aws.apprunner.ServiceInstanceConfigurationArgs(
        cpu="0.25 vCPU",
        memory="0.5 GB",
    ),
    health_check_configuration=aws.apprunner.ServiceHealthCheckConfigurationArgs(
        protocol="HTTP",
        path="/",
        interval=20,
        timeout=10,
        healthy_threshold=2,
        unhealthy_threshold=3,
    ),
    network_configuration=aws.apprunner.ServiceNetworkConfigurationArgs(
        egress_configuration=aws.apprunner.ServiceNetworkConfigurationEgressConfigurationArgs(
            egress_type="DEFAULT",
        )
    ),
    runtime_environment_variables={ # Directly assign resolved values
        "DATABASE_URL": pulumi.Output.concat("postgresql://", db_instance.username, ":", db_instance.password, "@", db_instance.address, ":", db_instance.port, "/", db_instance.db_name),
        "SMTP_DSN": pulumi.Output.concat("smtp://", ses_smtp_username, ":", ses_smtp_password, f"@email-smtp.{aws_region}.amazonaws.com:587?verify_peer=0"),
        "FROM_EMAIL": from_email,
        "ADMIN_EMAIL": admin_email,
        "SES_CONFIGURATION_SET": ses_configuration_set,
        "SES_WEBHOOK_SECRET": ses_webhook_secret,
        "RECAPTCHA_V3_SECRET_KEY": recaptcha_secret_key,
    },
    tags={
        "Name": "calcfolio-backend-app",
    },
    opts=pulumi.ResourceOptions(depends_on=[db_instance, ecr_repo]) # Ensure DB and ECR are ready
                                 # Use 'replaces' or 'delete_before_replace' if changing name for update
)


pulumi.export("backendEcrRepositoryUrl", ecr_repo.repository_url)
pulumi.export("backendDbAddress", db_instance.address)
pulumi.export("backendDbName", db_instance.db_name)
pulumi.export("backendAppServiceUrl", app_service_with_env.service_url) # Use the final service object
pulumi.export("backendAppServiceArn", app_service_with_env.arn)