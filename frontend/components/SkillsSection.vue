<template>
  <section class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto" :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'">
    <div class="w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-bold">My Skills</h2>
      <SectionDivider />
    </div>

    <!-- Category Selection -->
    <div class="mt-6 mb-8">
      <!-- Dropdown for small screens (<= 768px) -->
      <div v-if="isSmallScreen" class="relative dropdown-container">
        <button
          class="w-full px-4 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg font-medium transition-all duration-300 ease-in-out flex items-center justify-between hover:border-primary"
          :class="{ 'border-primary': dropdownOpen }"
          @click="toggleDropdown"
        >
          <div class="flex items-center">
            <Icon :name="currentCategoryData?.icon || 'lucide:code'" class="w-5 h-5 mr-3 text-primary" />
            <span>{{ currentCategoryData?.name || 'Select Category' }}</span>
            <span class="ml-2 text-xs opacity-75 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">({{ currentCategoryData?.skills?.length || 0 }})</span>
          </div>
          <Icon :name="dropdownOpen ? 'lucide:chevron-up' : 'lucide:chevron-down'" class="w-5 h-5 transition-transform duration-200" />
        </button>

        <!-- Dropdown Menu -->
        <transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 transform scale-95"
          enter-to-class="opacity-100 transform scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 transform scale-100"
          leave-to-class="opacity-0 transform scale-95"
        >
          <div
            v-if="dropdownOpen"
            class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
          >
            <div
              v-for="(category, categoryName) in skillCategories"
              :key="categoryName"
              class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-150"
              :class="{ 'bg-primary/10 dark:bg-primary/20': activeCategory === categoryName }"
              @click="selectCategory(categoryName)"
            >
              <Icon :name="category.icon" class="w-4 h-4 mr-3 text-gray-600 dark:text-gray-400" />
              <span class="flex-1">{{ category.name }}</span>
              <span class="text-xs opacity-75 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">({{ category.skills.length }})</span>
            </div>
          </div>
        </transition>
      </div>

      <!-- Buttons for larger screens (> 768px) -->
      <div v-else class="flex flex-wrap gap-3 justify-center md:justify-start">
        <button
          v-for="(category, categoryName) in skillCategories"
          :key="categoryName"
          :class="[
            'category-button',
            {
              'active': activeCategory === categoryName,
              'hovered': hoveredCategory === categoryName && !isMobile
            }
          ]"
          class="px-4 py-2 rounded-lg font-medium transition-all duration-300 ease-in-out transform border-2"
          @click="selectCategory(categoryName)"
          @mouseenter="handleCategoryHover(categoryName)"
          @mouseleave="handleCategoryLeave"
        >
          <Icon :name="category.icon" class="w-4 h-4 mr-2 inline-block" />
          {{ category.name }}
          <span class="category-count ml-1 text-xs opacity-75">({{ category.skills.length }})</span>
        </button>
      </div>
    </div>

    <!-- Skills Display -->
    <div class="relative overflow-visible">
      <!-- Category Description -->
      <div v-if="currentCategoryData" class="mb-6 p-4 bg-red-50/80 dark:bg-zinc-800 rounded-lg border border-red-100 dark:border-zinc-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
          <Icon :name="currentCategoryData.icon" class="w-5 h-5 mr-2 inline-block text-primary" />
          {{ currentCategoryData.name }}
        </h3>
        <p class="text-gray-600 dark:text-gray-300 text-sm">{{ currentCategoryData.description }}</p>
      </div>

      <!-- Skills Grid with Simplified Animation -->
      <transition-group
        name="skill-fade"
        tag="div"
        class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4 skills-grid"
      >
        <a
          v-for="(skill, index) in currentSkills"
          :key="showAllSkills ? `all-${skill}` : `${activeCategory}-${skill}`"
          :href="getSkillLink(skill)"
          :title="skill"
          target="_blank"
          rel="noopener noreferrer"
          class="skill-pill group"
          :style="{ animationDelay: showAllSkills ? '0ms' : `${Math.min(index * 25, 200)}ms` }"
        >
          <Icon :name="getSkillIcon(skill)" class="w-5 h-5 transition group-hover:text-primary" />
          <span class="text-sm font-medium">{{ skill }}</span>
        </a>
      </transition-group>

      <!-- Show All Skills Toggle -->
      <div class="text-center mt-8">
        <button
          class="inline-flex items-center px-6 py-2 bg-primary hover:bg-red-700 text-white font-medium rounded-lg border border-primary transition-colors duration-200"
          @click="toggleShowAll"
        >
          <Icon :name="showAllSkills ? 'lucide:eye-off' : 'lucide:eye'" class="w-4 h-4 mr-2" />
          {{ showAllSkills ? 'Hide All Skills' : 'Show All Skills' }}
        </button>
      </div>
    </div>
  </section>
</template>
  
<script setup lang="ts">
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import SectionDivider from './SectionDivider.vue'

  // Reactive state
  const activeCategory = ref('programming')
  const hoveredCategory = ref('')
  const showAllSkills = ref(false)
  const isMobile = ref(false)
  const isSmallScreen = ref(false)
  const dropdownOpen = ref(false)

  // Detect mobile device and small screen
  const checkIsMobile = () => {
    isMobile.value = window.innerWidth < 768 || !window.matchMedia('(pointer: fine)').matches
    isSmallScreen.value = window.innerWidth <= 768
  }

  onMounted(() => {
    checkIsMobile()
    window.addEventListener('resize', checkIsMobile)
    document.addEventListener('click', handleClickOutside)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', checkIsMobile)
    document.removeEventListener('click', handleClickOutside)
  })

  // Categorized skills data
  const skillCategories = ref({
    programming: {
      name: 'Programming Languages',
      icon: 'lucide:code',
      description: 'Core programming languages and runtime environments I use to build applications.',
      skills: ['JavaScript', 'TypeScript', 'Python', 'Java', 'Swift', 'PHP', 'Node.js']
    },
    frontend: {
      name: 'Frontend Frameworks',
      icon: 'lucide:monitor',
      description: 'Modern frontend frameworks and libraries for building user interfaces.',
      skills: ['React', 'Vue', 'Angular', 'Next.js', 'Nuxt.js']
    },
    mobile: {
      name: 'Mobile Development (iOS)',
      icon: 'lucide:smartphone',
      description: 'iOS development frameworks, tools, and technologies for building native mobile applications.',
      skills: ['SwiftUI', 'UIKit', 'Swift Data', 'Core Data', 'SQLite', 'TestFlight', 'Combine', 'CocoaPods', 'Swift Package Manager', 'Storyboards', 'Xcode', 'Swift Testing', 'XCTest', 'XCUITest', 'Core Location', 'Core Animation', 'AVFoundation', 'MapKit', 'HealthKit', 'WatchKit', 'WidgetKit', 'CloudKit', 'StoreKit', 'MVVM-C']
    },
    backend: {
      name: 'Backend & APIs',
      icon: 'lucide:server',
      description: 'Backend frameworks and technologies for server-side development.',
      skills: ['Express', 'NestJS', 'Flask', 'Django', 'Spring Boot', 'Spring Framework', 'Laravel', 'Symfony', 'CodeIgniter', 'RESTful APIs', 'GraphQL']
    },
    database: {
      name: 'Databases & ORMs',
      icon: 'lucide:database',
      description: 'Database systems and Object-Relational Mapping tools I work with.',
      skills: ['PostgreSQL', 'MySQL', 'MongoDB', 'DynamoDB', 'Redis', 'SQLite', 'TypeORM', 'Sequelize', 'Mongoose', 'Prisma', 'Knex.js', 'EloquentORM', 'SQLAlchemy', 'DjangoORM', 'Hibernate', 'Spring Data JPA']
    },
    data_bi: {
      name: 'Data & BI',
      icon: 'lucide:bar-chart-3',
      description: 'Data science, business intelligence, and data engineering tools for analysis and visualization.',
      skills: ['Excel', 'Power BI', 'Tableau', 'SQL', 'Pandas', 'NumPy', 'Seaborn', 'Matplotlib', 'Plotly', 'ELT', 'ETL', 'Apache Spark', 'Apache Airflow', 'BigQuery', 'Redshift', 'Snowflake', 'Data Studio', 'Looker', 'dbt', 'Airbyte', 'Fivetran']
    },
    testing: {
      name: 'Testing & Quality',
      icon: 'lucide:check-circle',
      description: 'Testing frameworks and methodologies for ensuring code quality.',
      skills: ['Jest', 'Vitest', 'Jasmine', 'Mocha', 'Cypress', 'Pytest', 'unittest', 'XCTest', 'PHPUnit', 'JUnit', 'TestNG', 'Mockito', 'TDD']
    },
    styling: {
      name: 'Styling & UI',
      icon: 'lucide:palette',
      description: 'CSS frameworks and UI libraries for creating beautiful interfaces.',
      skills: ['HTML', 'CSS', 'Sass', 'Tailwind CSS', 'Material UI', 'Bootstrap', 'Storybook.js']
    },
    tools: {
      name: 'Development Tools',
      icon: 'lucide:wrench',
      description: 'Package managers, build tools, and development utilities.',
      skills: ['npm', 'Yarn', 'bun', 'Composer', 'Maven', 'Gradle', 'SBT', 'IntelliJ IDEA', 'VS Code', 'Claude Code', 'Windsurf', 'Nova', 'Warp', 'Termius', 'Vite', 'Webpack', 'Git']
    },
    cloud: {
      name: 'Cloud & DevOps',
      icon: 'lucide:cloud',
      description: 'Cloud platforms and DevOps tools for deployment and infrastructure.',
      skills: ['AWS', 'Azure', 'Google Cloud', 'Railway', 'Vercel', 'Netlify', 'Heroku', 'Firebase', 'Docker', 'Kubernetes', 'AWS CDK', 'Pulumi', 'Terraform']
    },
    workflow: {
      name: 'Workflow & Platforms',
      icon: 'lucide:git-branch',
      description: 'Version control platforms and CI/CD tools for development workflow.',
      skills: ['Github', 'Gitlab', 'Bitbucket', 'Github Actions', 'Jenkins', 'Circle CI', 'Travis CI', 'CI/CD']
    },
    architecture: {
      name: 'Architecture & Patterns',
      icon: 'lucide:layers',
      description: 'Architectural patterns, methodologies, and emerging technologies.',
      skills: ['JVM', 'Microservices', 'Monoliths', 'Serverless', 'WebSockets', 'Redux', 'React Query', 'DevOps', 'Agile', 'Scrum', 'Generative AI']
    }
  })

  // Computed properties
  const currentCategoryData = computed(() => {
    const category = hoveredCategory.value && !isMobile.value ? hoveredCategory.value : activeCategory.value
    return skillCategories.value[category as keyof typeof skillCategories.value]
  })

  const currentSkills = computed(() => {
    if (showAllSkills.value) {
      return Object.values(skillCategories.value).flatMap(category => category.skills)
    }
    return currentCategoryData.value?.skills || []
  })

  // Methods
  const selectCategory = (categoryName: string) => {
    activeCategory.value = categoryName
    showAllSkills.value = false
    dropdownOpen.value = false // Close dropdown when category is selected
  }

  const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
  }

  // Close dropdown when clicking outside
  const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement
    if (dropdownOpen.value && !target.closest('.dropdown-container')) {
      dropdownOpen.value = false
    }
  }

  const handleCategoryHover = (categoryName: string) => {
    // Disable hover switching when showing all skills or on mobile
    if (!isMobile.value && !showAllSkills.value) {
      hoveredCategory.value = categoryName
    }
  }

  const handleCategoryLeave = () => {
    if (!isMobile.value && !showAllSkills.value) {
      hoveredCategory.value = ''
    }
  }

  const toggleShowAll = () => {
    showAllSkills.value = !showAllSkills.value
    // Clear hover state when toggling
    hoveredCategory.value = ''
  }

  const skillLinks: Record<string, string> = {
    'Java': 'https://docs.oracle.com/en/java/',
    'Spring Boot': 'https://spring.io/projects/spring-boot',
    'Spring Framework': 'https://spring.io/projects/spring-framework',
    'Hibernate': 'https://hibernate.org/orm/documentation/',
    'Spring Data JPA': 'https://spring.io/projects/spring-data-jpa',
    'JUnit': 'https://junit.org/junit5/docs/current/user-guide/',
    'TestNG': 'https://testng.org/doc/documentation-main.html',
    'Mockito': 'https://javadoc.io/doc/org.mockito/mockito-core/latest/org/mockito/Mockito.html',
    'Maven': 'https://maven.apache.org/guides/index.html',
    'Gradle': 'https://docs.gradle.org/',
    'JVM': 'https://docs.oracle.com/en/java/javase/21/',
    'SBT': 'https://www.scala-sbt.org/documentation.html',
    'IntelliJ IDEA': 'https://www.jetbrains.com/idea/documentation/',
    'VS Code': 'https://code.visualstudio.com/docs',
    'Claude Code': 'https://docs.anthropic.com/en/docs/claude-code',
    'Windsurf': 'https://codeium.com/windsurf',
    'Nova': 'https://nova.app/help/',
    'Warp': 'https://docs.warp.dev/',
    'Termius': 'https://termius.com/help/',
    'Jenkins': 'https://www.jenkins.io/doc/',
    'Circle CI': 'https://circleci.com/docs/',
    'Travis CI': 'https://docs.travis-ci.com/',
    'Cypress': 'https://docs.cypress.io/',
    'Azure': 'https://docs.microsoft.com/en-us/azure/',
    'Railway': 'https://docs.railway.app/',
    'Swift': 'https://swift.org/documentation/',
    'SwiftUI': 'https://developer.apple.com/documentation/swiftui/',
    'UIKit': 'https://developer.apple.com/documentation/uikit/',
    'Express': 'https://expressjs.com/',
    'React': 'https://reactjs.org/',
    'NestJS': 'https://docs.nestjs.com/',
    'TypeScript': 'https://www.typescriptlang.org/docs/',
    'iOS Development': 'https://developer.apple.com/documentation/',
    'Jest': 'https://jestjs.io/docs/getting-started',
    'Mocha': 'https://mochajs.org/',
    'Pytest': 'https://docs.pytest.org/',
    'Docker': 'https://docs.docker.com/',
    'Ansible': 'https://docs.ansible.com/',
    'MySQL': 'https://dev.mysql.com/doc/',
    'MongoDB': 'https://www.mongodb.com/docs/',
    'Github Actions': 'https://docs.github.com/en/actions',
    'Kubernetes': 'https://kubernetes.io/docs/home/',
    'JavaScript': 'https://developer.mozilla.org/en-US/docs/Web/JavaScript',
    'Next.js': 'https://nextjs.org/docs',
    'Node.js': 'https://nodejs.org/en/docs',
    'Python': 'https://docs.python.org/3/',
    'Flask': 'https://flask.palletsprojects.com/',
    'Django': 'https://docs.djangoproject.com/',
    'RESTful APIs': 'https://restfulapi.net/',
    'PostgreSQL': 'https://www.postgresql.org/docs/',
    'GraphQL': 'https://graphql.org/learn/',
    'HTML': 'https://developer.mozilla.org/en-US/docs/Web/HTML',
    'CSS': 'https://developer.mozilla.org/en-US/docs/Web/CSS',
    'Git': 'https://git-scm.com/doc',
    'AWS': 'https://docs.aws.amazon.com/',
    'JQuery': 'https://api.jquery.com/',
    'Tailwind CSS': 'https://tailwindcss.com/docs',
    'Sass': 'https://sass-lang.com/documentation/',
    'PHP': 'https://www.php.net/docs.php',
    'AWS CDK': 'https://docs.aws.amazon.com/cdk/latest/guide/home.html',
    'Bootstrap': 'https://getbootstrap.com/docs/',
    'Pulumi': 'https://www.pulumi.com/docs/',
    'Terraform': 'https://developer.hashicorp.com/terraform/docs',
    'Material UI': 'https://mui.com/material-ui/getting-started/overview/',
    'Vue': 'https://vuejs.org/guide/introduction.html',
    'Nuxt.js': 'https://nuxt.com/docs',
    'Laravel': 'https://laravel.com/docs',
    'Symfony': 'https://symfony.com/doc/current/index.html',
    'CodeIgniter': 'https://codeigniter.com/user_guide/',
    'Composer': 'https://getcomposer.org/doc/',
    'npm': 'https://docs.npmjs.com/',
    'Yarn': 'https://classic.yarnpkg.com/en/docs/getting-started/installation/',
    'bun': 'https://bun.sh/docs',
    'Angular': 'https://angular.io/docs',
    'Storybook.js': 'https://storybook.js.org/docs/get-started/install',
    'Generative AI': '',
    'Monoliths': '',
    'DevOps': 'https://aws.amazon.com/devops/what-is-devops/',
    'Agile': 'https://www.agilealliance.org/agile101/',
    'Scrum': 'https://www.scrum.org/resources/what-is-scrum',
    'TDD': 'https://www.agilealliance.org/glossary/tdd/',
    'Serverless': 'https://aws.amazon.com/serverless/',
    'Microservices': 'https://microservices.io/patterns/microservices.html',
    'WebSockets': 'https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API',
    'CI/CD': 'https://www.redhat.com/en/topics/devops/what-is-ci-cd',
    'DynamoDB': 'https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/Introduction.html',
    'Redis': 'https://redis.io/documentation',
    'SQLite': 'https://www.sqlite.org/docs.html',
    'Excel': 'https://www.microsoft.com/en-us/microsoft-365/excel',
    'Power BI': 'https://powerbi.microsoft.com/',
    'Tableau': 'https://www.tableau.com/',
    'SQL': 'https://www.w3schools.com/sql/',
    'Pandas': 'https://pandas.pydata.org/',
    'NumPy': 'https://numpy.org/',
    'Seaborn': 'https://seaborn.pydata.org/',
    'Matplotlib': 'https://matplotlib.org/',
    'Plotly': 'https://plotly.com/',
    'ELT': 'https://www.stitchdata.com/resources/elt-vs-etl/',
    'ETL': 'https://www.talend.com/resources/what-is-etl/',
    'Apache Spark': 'https://spark.apache.org/',
    'Apache Airflow': 'https://airflow.apache.org/',
    'BigQuery': 'https://cloud.google.com/bigquery',
    'Redshift': 'https://aws.amazon.com/redshift/',
    'Snowflake': 'https://www.snowflake.com/',
    'Data Studio': 'https://datastudio.google.com/',
    'Looker': 'https://looker.com/',
    'dbt': 'https://www.getdbt.com/',
    'Airbyte': 'https://airbyte.com/',
    'Fivetran': 'https://fivetran.com/',
    'Swift Data': 'https://developer.apple.com/documentation/swiftdata/',
    'Core Data': 'https://developer.apple.com/documentation/coredata/',
    'TestFlight': 'https://developer.apple.com/testflight/',
    'Combine': 'https://developer.apple.com/documentation/combine/',
    'CocoaPods': 'https://cocoapods.org/',
    'Swift Package Manager': 'https://swift.org/package-manager/',
    'Storyboards': 'https://developer.apple.com/library/archive/documentation/General/Conceptual/Devpedia-CocoaApp/Storyboard.html',
    'Xcode': 'https://developer.apple.com/xcode/',
    'Swift Testing': 'https://developer.apple.com/documentation/testing/',
    'XCUITest': 'https://developer.apple.com/documentation/xctest/xcuielement/',
    'Core Location': 'https://developer.apple.com/documentation/corelocation/',
    'Core Animation': 'https://developer.apple.com/documentation/quartzcore/',
    'AVFoundation': 'https://developer.apple.com/documentation/avfoundation/',
    'MapKit': 'https://developer.apple.com/documentation/mapkit/',
    'HealthKit': 'https://developer.apple.com/documentation/healthkit/',
    'WatchKit': 'https://developer.apple.com/documentation/watchkit/',
    'WidgetKit': 'https://developer.apple.com/documentation/widgetkit/',
    'CloudKit': 'https://developer.apple.com/documentation/cloudkit/',
    'StoreKit': 'https://developer.apple.com/documentation/storekit/',
    'MVVM-C': 'https://developer.apple.com/documentation/uikit/mvc',
    'TypeORM': 'https://typeorm.io/#/',
    'Sequelize': 'https://sequelize.org/master/manual/getting-started.html',
    'Mongoose': 'https://mongoosejs.com/docs/index.html',
    'Prisma': 'https://www.prisma.io/docs/getting-started',
    'Knex.js': 'https://knexjs.org/#Installation',
    'Github': 'https://github.com/',
    'Gitlab': 'https://gitlab.com/',
    'Bitbucket': 'https://bitbucket.org/',
    'Vercel': 'https://vercel.com/',
    'Netlify': 'https://www.netlify.com/',
    'Heroku': 'https://www.heroku.com/',
    'Firebase': 'https://firebase.google.com/docs',
    'Google Cloud': 'https://cloud.google.com/docs',
    'PHPUnit': 'https://phpunit.de/index.html',
    'XCTest': 'https://developer.apple.com/documentation/xctest',
    'unittest': 'https://docs.python.org/3/library/unittest.html',
    'Jasmine': 'https://jasmine.github.io/',
    'Redux': 'https://redux.js.org/introduction/getting-started',
    'React Query': 'https://react-query.tanstack.com/overview',
    'EloquentORM': 'https://laravel.com/docs/eloquent',
    'SQLAlchemy': 'https://docs.sqlalchemy.org/en/20/index.html',
    'DjangoORM': 'https://docs.djangoproject.com/en/stable/topics/db/models/',
    'Vitest': 'https://vitest.dev/guide/',
    'Vite': 'https://vitejs.dev/guide/',
    'Webpack': 'https://webpack.js.org/concepts/',
  }

  const iconMap: Record<string, string> = {
    'Java': 'simple-icons:openjdk',
    'Spring Boot': 'simple-icons:spring',
    'Spring Framework': 'simple-icons:spring',
    'Hibernate': 'simple-icons:hibernate',
    'Spring Data JPA': 'simple-icons:spring',
    'JUnit': 'simple-icons:junit5',
    'TestNG': 'lucide:check-circle',
    'Mockito': 'lucide:shield-check',
    'Maven': 'simple-icons:apachemaven',
    'Gradle': 'simple-icons:gradle',
    'JVM': 'simple-icons:openjdk',
    'SBT': 'simple-icons:scala',
    'IntelliJ IDEA': 'simple-icons:intellijidea',
    'VS Code': 'simple-icons:visualstudiocode',
    'Claude Code': 'simple-icons:anthropic',
    'Windsurf': 'simple-icons:codeium',
    'Nova': 'simple-icons:nova',
    'Warp': 'simple-icons:warp',
    'Termius': 'lucide:terminal',
    'Jenkins': 'simple-icons:jenkins',
    'Circle CI': 'simple-icons:circleci',
    'Travis CI': 'simple-icons:travisci',
    'Cypress': 'simple-icons:cypress',
    'Azure': 'simple-icons:microsoftazure',
    'Railway': 'simple-icons:railway',
    'Swift': 'simple-icons:swift',
    'SwiftUI': 'simple-icons:apple',
    'UIKit': 'simple-icons:uikit',
    'Express': 'simple-icons:express',
    'React': 'simple-icons:react',
    'NestJS': 'simple-icons:nestjs',
    'TypeScript': 'simple-icons:typescript',
    'iOS Development': 'lucide:smartphone',
    'Jest': 'simple-icons:jest',
    'Mocha': 'simple-icons:mocha',
    'Pytest': 'simple-icons:pytest',
    'Docker': 'simple-icons:docker',
    'Ansible': 'simple-icons:ansible',
    'MySQL': 'simple-icons:mysql',
    'MongoDB': 'simple-icons:mongodb',
    'Github Actions': 'simple-icons:githubactions',
    'Kubernetes': 'simple-icons:kubernetes',
    'JavaScript': 'simple-icons:javascript',
    'Next.js': 'simple-icons:nextdotjs',
    'Node.js': 'simple-icons:nodedotjs',
    'Python': 'simple-icons:python',
    'Flask': 'simple-icons:flask',
    'Django': 'simple-icons:django',
    'RESTful APIs': 'lucide:server',
    'PostgreSQL': 'simple-icons:postgresql',
    'GraphQL': 'simple-icons:graphql',
    'HTML': 'simple-icons:html5',
    'CSS': 'simple-icons:css3',
    'Git': 'simple-icons:git',
    'AWS': 'simple-icons:amazonaws',
    'JQuery': 'simple-icons:jquery',
    'Tailwind CSS': 'simple-icons:tailwindcss',
    'Sass': 'simple-icons:sass',
    'PHP': 'simple-icons:php',
    'AWS CDK': 'simple-icons:amazonaws',
    'Bootstrap': 'simple-icons:bootstrap',
    'Pulumi': 'simple-icons:pulumi',
    'Terraform': 'simple-icons:terraform',
    'Material UI': 'simple-icons:mui',
    'Vue': 'simple-icons:vuedotjs',
    'Nuxt.js': 'simple-icons:nuxtdotjs',
    'Laravel': 'simple-icons:laravel',
    'Symfony': 'simple-icons:symfony',
    'CodeIgniter': 'simple-icons:codeigniter',
    'Composer': 'simple-icons:composer',
    'Angular': 'simple-icons:angular',
    'bun': 'simple-icons:bun',
    'npm': 'simple-icons:npm',
    'Yarn': 'simple-icons:yarn',
    'Serverless': 'simple-icons:serverless',
    'DynamoDB': 'simple-icons:amazondynamodb',
    'Redis': 'simple-icons:redis',
    'SQLite': 'simple-icons:sqlite',
    'TypeORM': 'simple-icons:typeorm',
    'Sequelize': 'simple-icons:sequelize',
    'Mongoose': 'simple-icons:mongoose',
    'Prisma': 'simple-icons:prisma',
    'Knex.js': 'simple-icons:knexdotjs',
    'Github': 'simple-icons:github',
    'Gitlab': 'simple-icons:gitlab',
    'Bitbucket': 'simple-icons:bitbucket',
    'Vercel': 'simple-icons:vercel',
    'Netlify': 'simple-icons:netlify',
    'Heroku': 'simple-icons:heroku',
    'Firebase': 'simple-icons:firebase',
    'Google Cloud': 'simple-icons:googlecloud',
    'Storybook.js': 'simple-icons:storybook',
    'Jasmine': 'simple-icons:jasmine',
    'PHPUnit': 'simple-icons:php',
    'XCTest': 'simple-icons:swift',
    'unittest': 'simple-icons:python',
    'Redux': 'simple-icons:redux',
    'React Query': 'simple-icons:reactquery',
    'EloquentORM': 'simple-icons:laravel',
    'SQLAlchemy': 'simple-icons:sqlalchemy',
    'DjangoORM': 'simple-icons:django',
    'Vitest': 'simple-icons:vitest',
    'Vite': 'simple-icons:vite',
    'Webpack': 'simple-icons:webpack',
    'Excel': 'simple-icons:microsoftexcel',
    'Power BI': 'simple-icons:powerbi',
    'Tableau': 'simple-icons:tableau',
    'SQL': 'lucide:database',
    'Pandas': 'simple-icons:pandas',
    'NumPy': 'simple-icons:numpy',
    'Seaborn': 'lucide:bar-chart',
    'Matplotlib': 'lucide:trending-up',
    'Plotly': 'simple-icons:plotly',
    'ELT': 'lucide:arrow-right-left',
    'ETL': 'lucide:refresh-cw',
    'Apache Spark': 'simple-icons:apachespark',
    'Apache Airflow': 'simple-icons:apacheairflow',
    'BigQuery': 'simple-icons:googlecloud',
    'Redshift': 'simple-icons:amazonaws',
    'Snowflake': 'simple-icons:snowflake',
    'Data Studio': 'simple-icons:google',
    'Looker': 'simple-icons:looker',
    'dbt': 'simple-icons:dbt',
    'Airbyte': 'lucide:cloud',
    'Fivetran': 'lucide:zap',
    'Swift Data': 'simple-icons:apple',
    'Core Data': 'simple-icons:apple',
    'TestFlight': 'simple-icons:apple',
    'Combine': 'simple-icons:apple',
    'CocoaPods': 'simple-icons:cocoapods',
    'Swift Package Manager': 'simple-icons:apple',
    'Storyboards': 'simple-icons:apple',
    'Xcode': 'simple-icons:xcode',
    'Swift Testing': 'simple-icons:apple',
    'XCUITest': 'simple-icons:apple',
    'Core Location': 'simple-icons:apple',
    'Core Animation': 'simple-icons:apple',
    'AVFoundation': 'simple-icons:apple',
    'MapKit': 'simple-icons:apple',
    'HealthKit': 'simple-icons:apple',
    'WatchKit': 'simple-icons:apple',
    'WidgetKit': 'simple-icons:apple',
    'CloudKit': 'simple-icons:apple',
    'StoreKit': 'simple-icons:apple',
    'MVVM-C': 'lucide:layers',
    'Generative AI': '',
    'Monoliths': '',
    'Microservices': '',
    'DevOps': '',
    'Agile': '',
    'Scrum': '',
    'TDD': '',
    'WebSockets': '',
    'CI/CD': '',
  }

  const getSkillLink = (skill: string) => skillLinks[skill] || '#'

  const getSkillIcon = (skill: string) => {
    const icon = iconMap[skill]
    if (skill === 'Java' && !icon) {
      // Fallback for Java if simple-icons doesn't have it
      return 'lucide:coffee'
    }
    return icon || 'lucide:code'
  }
</script>

<style scoped>
.category-button {
  background: rgb(255 255 255);
  border-color: rgb(209 213 219);
  color: rgb(55 65 81);
}

.category-button:hover {
  background: rgb(255 255 255);
  border-color: rgb(244 63 94 / 0.5);
  transform: translateY(-1px);
  box-shadow: 0 6px 12px rgb(0 0 0 / 0.08);
}

.category-button.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
  box-shadow: 0 6px 14px rgb(245 71 71 / 0.28);
}

.category-button.hovered {
  border-color: rgb(244 63 94 / 0.4);
  background: rgb(254 242 242);
}

.category-count {
  background: rgb(249 250 251);
  color: rgb(75 85 99);
  border-radius: 0.375rem;
  padding: 0.125rem 0.375rem;
  font-weight: 600;
}

.dark .category-button {
  background: rgb(24 24 27);
  border-color: rgb(63 63 70);
  color: rgb(212 212 216);
}

.dark .category-button:hover {
  background: rgb(24 24 27);
  border-color: rgb(245 71 71 / 0.7);
}

.dark .category-button.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.dark .category-button.hovered {
  background: rgb(63 18 18);
  border-color: rgb(185 28 28);
}

.dark .category-count {
  background: rgb(39 39 42);
  color: rgb(212 212 216);
}

.skill-fade-enter-active {
  transition: all 0.2s ease;
}

.skill-fade-leave-active {
  transition: all 0.15s ease;
}

.skill-fade-enter-from {
  opacity: 0;
  transform: translateY(10px) scale(0.95);
}

.skill-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.skill-fade-move {
  transition: transform 0.2s ease;
}

.skill-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
  padding: 1rem 0.25rem;
  border-radius: 0.65rem;
  border: 1px solid rgb(229 231 235);
  background-color: rgb(255 255 255);
  cursor: pointer;
  transition: all 0.2s ease;
  animation: slideInUp 0.2s ease forwards;
  opacity: 0;
  transform: translateY(6px);
}

.skill-pill:hover {
  border-color: rgb(245 71 71 / 0.5);
  background: rgb(254 250 250);
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgb(0 0 0 / 0.12);
  z-index: 10;
}

.dark .skill-pill {
  border-color: rgb(63 63 70);
  background-color: rgb(24 24 27);
}

.dark .skill-pill:hover {
  border-color: rgb(245 71 71 / 0.6);
  background: rgb(39 39 42);
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgb(0 0 0 / 0.25);
  z-index: 10;
}

.skills-grid {
  overflow: visible;
}

.skills-grid .skill-pill {
  position: relative;
}

@keyframes slideInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
