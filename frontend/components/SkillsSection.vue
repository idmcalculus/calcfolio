<template>
  <section class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto" :data-aos="$nuxt.isHydrating ? undefined : 'fade-up'">
    <div class="w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-bold">My Skills</h2>
      <SectionDivider />
    </div>

    <!-- Category Selection -->
    <div class="flex flex-wrap gap-3 mt-6 mb-8 justify-center md:justify-start">
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

    <!-- Skills Display -->
    <div class="relative overflow-visible">
      <!-- Category Description -->
      <div v-if="currentCategoryData" class="mb-6 p-4 bg-linear-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
          <Icon :name="currentCategoryData.icon" class="w-5 h-5 mr-2 inline-block text-blue-600 dark:text-blue-400" />
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
          class="inline-flex items-center px-6 py-2 bg-linear-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
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

  // Detect mobile device
  const checkIsMobile = () => {
    isMobile.value = window.innerWidth < 768 || !window.matchMedia('(pointer: fine)').matches
  }

  onMounted(() => {
    checkIsMobile()
    window.addEventListener('resize', checkIsMobile)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', checkIsMobile)
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
      skills: ['React', 'Vue', 'Angular', 'Next.js', 'Nuxt.js', 'SwiftUI', 'UIKit']
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

@keyframes gradientMove {
  0% {
    background-position: 0% 0%;
  }
  50% {
    background-position: 100% 100%;
  }
  100% {
    background-position: 0% 0%;
  }
}

.category-button {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-color: #cbd5e1;
  color: #475569;
  position: relative;
  overflow: hidden;
}

.category-button:hover {
  background: 
    linear-gradient(#f8fafc, #f8fafc) padding-box,
    linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.category-button.active {
  background: 
    linear-gradient(#3b82f6, #1d4ed8) padding-box,
    linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.category-button.hovered {
  background: 
    linear-gradient(#ddd6fe, #c4b5fd) padding-box,
    linear-gradient(45deg, #8b5cf6, #3b82f6, #ec4899, #f59e0b, #8b5cf6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.category-count {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 0.375rem;
  padding: 0.125rem 0.375rem;
  font-weight: 600;
}

.dark .category-button {
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  border-color: #374151;
  color: #d1d5db;
}

.dark .category-button:hover {
  background: 
    linear-gradient(#1f2937, #1f2937) padding-box,
    linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
}

.dark .category-button.active {
  background: 
    linear-gradient(#3b82f6, #1d4ed8) padding-box,
    linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
  color: white;
}

.dark .category-button.hovered {
  background: 
    linear-gradient(#581c87, #7c3aed) padding-box,
    linear-gradient(45deg, #8b5cf6, #3b82f6, #ec4899, #f59e0b, #8b5cf6) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  animation: gradientMove 3s ease infinite;
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
  border-radius: 0.5rem;
  border: 2px solid #e5e7eb;
  background-color: #fff;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  animation: slideInUp 0.3s ease forwards;
  opacity: 0;
  transform: translateY(10px);
}

.skill-pill::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6);
  background-size: 300% 300%;
  border-radius: 0.5rem;
  z-index: -1;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.skill-pill:hover::before {
  opacity: 0.25;
  animation: gradientMove 3s ease infinite;
}

.skill-pill:hover {
  background: #fafafa;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  z-index: 10;
}

.dark .skill-pill {
  border-color: #3f3f46;
  background-color: #1e1e1e;
}

.dark .skill-pill:hover {
  background: #1e1e1e;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
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

.dark .skill-pill {
  border-color: #3f3f46;
  background-color: #1e1e1e;
}
</style>