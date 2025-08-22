import pulumi
import pulumi_aws as aws
import os
import mimetypes # For guessing content types

config = pulumi.Config()
# custom_domain_name = config.get("customDomainName")
# acm_certificate_arn = config.get("acmCertificateArn") # Must be in us-east-1 for CloudFront

# S3 bucket for static website content
site_bucket = aws.s3.BucketV2("siteBucket",
    bucket=f"calcfolio-frontend-site-{pulumi.get_stack()}",
)

# Block public access as CloudFront will use an Origin Access Identity
aws.s3.BucketPublicAccessBlock("siteBucketPublicAccessBlock",
    bucket=site_bucket.id,
    block_public_acls=True,
    block_public_policy=True,
    ignore_public_acls=True,
    restrict_public_buckets=True
)

# Origin Access Identity for CloudFront
origin_access_identity = aws.cloudfront.OriginAccessIdentity("oai",
    comment="OAI for Calcfolio frontend S3 bucket"
)

# Grant CloudFront OAI permissions to read from S3
bucket_policy_document = pulumi.Output.all(site_bucket.arn, origin_access_identity.iam_arn).apply(
    lambda args: aws.iam.get_policy_document(statements=[
        aws.iam.GetPolicyDocumentStatementArgs(
            effect="Allow",
            principals=[aws.iam.GetPolicyDocumentStatementPrincipalArgs(
                type="AWS",
                identifiers=[args[1]], # OAI IAM ARN
            )],
            actions=["s3:GetObject"],
            resources=[f"{args[0]}/*"], # Bucket ARN
        )
    ]).json
)

aws.s3.BucketPolicy("bucketPolicy",
    bucket=site_bucket.bucket, # Use .bucket for the name
    policy=bucket_policy_document
)

# Path to the local directory containing the built frontend assets
# Assumes Nuxt build output is in `../.output/public` relative to this `infra` folder
site_dir = os.path.join(os.path.dirname(__file__), "../.output/public")

# Upload site contents
if os.path.exists(site_dir):
    for item in os.listdir(site_dir):
        item_path = os.path.join(site_dir, item)
        if os.path.isfile(item_path):
            # Determine content type
            content_type, _ = mimetypes.guess_type(item_path)
            aws.s3.BucketObject(item, # Use item as the resource name for Pulumi
                bucket=site_bucket.id,
                source=pulumi.FileAsset(item_path),
                key=item, # S3 object key
                content_type=content_type or "application/octet-stream",
                acl="private", # Since using OAI
                opts=pulumi.ResourceOptions(parent=site_bucket)
            )
else:
    pulumi.log.warn(f"Frontend build directory {site_dir} not found. S3 bucket will be empty.")

# CloudFront distribution
cdn = aws.cloudfront.Distribution("cdn",
    enabled=True,
    origins=[aws.cloudfront.DistributionOriginArgs(
        origin_id=site_bucket.arn,
        domain_name=site_bucket.bucket_regional_domain_name,
        s3_origin_config=aws.cloudfront.DistributionOriginS3OriginConfigArgs(
            origin_access_identity=origin_access_identity.cloudfront_access_identity_path,
        ),
    )],
    default_root_object="index.html",
    default_cache_behavior=aws.cloudfront.DistributionDefaultCacheBehaviorArgs(
        target_origin_id=site_bucket.arn,
        viewer_protocol_policy="redirect-to-https",
        allowed_methods=["GET", "HEAD", "OPTIONS"],
        cached_methods=["GET", "HEAD", "OPTIONS"],
        forwarded_values=aws.cloudfront.DistributionDefaultCacheBehaviorForwardedValuesArgs(
            cookies=aws.cloudfront.DistributionDefaultCacheBehaviorForwardedValuesCookiesArgs(forward="none"),
            query_string=False,
        ),
        min_ttl=0,
        default_ttl=3600,
        max_ttl=86400,
        compress=True,
    ),
    price_class="PriceClass_100",
    viewer_certificate=aws.cloudfront.DistributionViewerCertificateArgs(
        cloudfront_default_certificate=True,
    ),
    # aliases= [custom_domain_name] if custom_domain_name else [],
    restrictions=aws.cloudfront.DistributionRestrictionsArgs(
        geo_restriction=aws.cloudfront.DistributionRestrictionsGeoRestrictionArgs(
            restriction_type="none",
        ),
    ),
    custom_error_responses=[
        aws.cloudfront.DistributionCustomErrorResponseArgs(error_code=403, response_code=200, response_page_path="/index.html", error_caching_min_ttl=10),
        aws.cloudfront.DistributionCustomErrorResponseArgs(error_code=404, response_code=200, response_page_path="/index.html", error_caching_min_ttl=10),
    ],
    tags={
        "Name": "calcfolio-frontend-cdn",
    }
)

pulumi.export("s3BucketName", site_bucket.bucket)
pulumi.export("s3BucketRegionalDomainName", site_bucket.bucket_regional_domain_name)
pulumi.export("cloudfrontDistributionId", cdn.id)
pulumi.export("cloudfrontDomainName", cdn.domain_name)
pulumi.export("websiteUrl", pulumi.Output.concat("https://", cdn.domain_name))