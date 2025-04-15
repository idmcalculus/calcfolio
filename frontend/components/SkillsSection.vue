<template>
	<section class="my-12 px-6 max-w-screen-xl mx-auto" data-aos="fade-up">
    <div class="w-full text-center md:text-left">
      <h2 class="text-2xl md:text-3xl font-bold">My Skills</h2>
      <SectionDivider />
    </div>
	  <div class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4 mt-6">
      <a
        v-for="(skill, index) in skills"
        :key="index"
        :href="getSkillLink(skill)"
        :title="skill"
        target="_blank"
        rel="noopener noreferrer"
        class="skill-pill group"
      >
        <Icon :name="getSkillIcon(skill)" class="w-5 h-5 transition group-hover:text-primary" />
        <span class="text-sm font-medium">{{ skill }}</span>
      </a>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import AOS from 'aos'
  import SectionDivider from './SectionDivider.vue'
  
  onMounted(() => AOS.init({ duration: 800 }))
  
  const skills = ref([
    'JavaScript', 'TypeScript', 'Node.js', 'React', 'Vue', 'Angular', 'Next.js', 'NestJS', 'Nuxt.js', 'Express', 'JQuery',
    'Python', 'Flask', 'Django', 'Swift', 'SwiftUI', 'UIKit', 'PHP', 'Laravel', 'Symfony', 'CodeIgniter', 'Composer',
    'npm', 'Yarn', 'bun', 'HTML', 'CSS', 'Sass', 'Tailwind CSS', 'Material UI', 'Bootstrap', 'Storybook.js',
    'Jest', 'Jasmine', 'Mocha', 'Pytest', 'unittest', 'XCTest', 'PHPUnit', 'PostgreSQL', 'MySQL', 'MongoDB', 'DynamoDB',
    'Redis', 'SQLite', 'TypeORM', 'Sequelize', 'Mongoose', 'Prisma', 'Knex.js', 'RESTful APIs', 'GraphQL', 'Git',
    'AWS', 'Google Cloud', 'Vercel', 'Netlify', 'Heroku', 'Firebase', 'Github', 'Gitlab', 'Bitbucket', 'CI/CD',
    'AWS CDK', 'Pulumi', 'Terraform',  'Github Actions', 'Docker', 'Kubernetes', 'WebSockets',
    'Serverless', 'Microservices', 'Monoliths', 'DevOps', 'Agile', 'Scrum', 'TDD', 'Generative AI'
  ])

  const skillLinks: Record<string, string> = {
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
  }

  const iconMap: Record<string, string> = {
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
    'Pytest': 'simple-icons:python',
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
    'DynamoDB': 'simple-icons:dynamodb',
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
    'Generative AI': '',
    'PHPUnit': 'simple-icons:php',
    'XCTest': 'simple-icons:swift',
    'unittest': 'simple-icons:python',
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

  const getSkillIcon = (skill: string) => iconMap[skill] || 'lucide:code'
</script>

<style scoped>
@keyframes glitterBorder {
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
}

.skill-pill:hover {
  background:
    linear-gradient(#fafafa, #ffffff) padding-box,
    linear-gradient(300deg, #9845E8, #33D2FF, #DD5789, #9845E8) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  background-origin: border-box;
  background-clip: padding-box, border-box;
  animation: glitterBorder 3s ease infinite;
  transform: scale(1.05);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  z-index: 10;
}

.dark .skill-pill {
  border-color: #3f3f46;
  background-color: #1e1e1e;
}

.dark .skill-pill:hover {
  background:
    linear-gradient(#1e1e1e, #1e1e1e) padding-box,
    linear-gradient(300deg, #9845E8, #33D2FF, #DD5789, #9845E8) border-box;
  border: 2px solid transparent;
  background-size: 300% 300%;
  background-origin: border-box;
  background-clip: padding-box, border-box;
  animation: glitterBorder 3s ease infinite;
}
</style>