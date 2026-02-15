export interface Credential {
  id: string
  title: string
  provider: string
  issuedAt: string
  verificationUrl: string
  imageUrl?: string
  providerIcon?: string
  featured?: boolean
}

export const credentials: Credential[] = [
  {
    id: 'udemy-uc-b5ea4a9f-496c-4300-b570-a0129dea02eb',
    title: 'AI Engineer Core Track: LLM Engineering, RAG, QLoRA, Agents',
    provider: 'Udemy',
    issuedAt: 'September 30, 2025',
    verificationUrl: 'https://ude.my/UC-b5ea4a9f-496c-4300-b570-a0129dea02eb/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771028775/Udemy/AI%20Engineer%20Core%20Track:%20LLM%20Engineering%2C%20RAG%2C%20QLoRA%2C%20Agents.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-b1075a72-c540-475f-a5f6-72fc097dc1e7',
    title: 'Public Speaking and Presenting at Work',
    provider: 'Udemy',
    issuedAt: 'October 14, 2022',
    verificationUrl: 'https://ude.my/UC-b1075a72-c540-475f-a5f6-72fc097dc1e7/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771016989/Udemy/Public%20Speaking%20and%20Presenting%20at%20Work.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: false
  },
  {
    id: 'udemy-uc-7f66f470-8045-4967-a5ea-ccc58a49c181',
    title: 'Core Data in iOS',
    provider: 'Udemy',
    issuedAt: 'April 13, 2022',
    verificationUrl: 'https://ude.my/UC-7f66f470-8045-4967-a5ea-ccc58a49c181/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1726244556/Udemy/Core%20Data%20in%20iOS.pdf',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-060e9782-a281-4962-90c4-f666b8c17d7f',
    title: 'Unit Testing Swift Mobile App',
    provider: 'Udemy',
    issuedAt: 'April 6, 2022',
    verificationUrl: 'https://ude.my/UC-060e9782-a281-4962-90c4-f666b8c17d7f/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1726244553/Udemy/Unit%20Testing%20Swift%20Mobile%20App.pdf',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-3cf1b966-4f79-4354-b8bd-9f97fc2328d2',
    title: 'Data Structures and Algorithms in Swift',
    provider: 'Udemy',
    issuedAt: 'March 31, 2022',
    verificationUrl: 'https://ude.my/UC-3cf1b966-4f79-4354-b8bd-9f97fc2328d2/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771019738/Udemy/Data%20Structures%20and%20Algorithms%20in%20Swift.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-5b67a911-4d6a-44be-8f1e-8e7c5f828bd6',
    title: 'MVVM Design Pattern Using Swift in iOS',
    provider: 'Udemy',
    issuedAt: 'December 27, 2023',
    verificationUrl: 'https://ude.my/UC-5b67a911-4d6a-44be-8f1e-8e7c5f828bd6/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771019457/Udemy/MVVM%20Design%20Pattern%20Using%20Swift%20in%20iOS.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-e7147060-ae24-4d73-85f5-897cc8efd650',
    title: 'SwiftUI Masterclass 2022',
    provider: 'Udemy',
    issuedAt: 'March 25, 2022',
    verificationUrl: 'https://ude.my/UC-e7147060-ae24-4d73-85f5-897cc8efd650/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771024974/Udemy/SwiftUI%20Masterclass%202022.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-90ff2d74-6691-4929-847d-fd1046a3e3e4',
    title: 'Wordpress for Beginners - Master Wordpress Quickly',
    provider: 'Udemy',
    issuedAt: 'October 5, 2020',
    verificationUrl: 'https://ude.my/UC-90ff2d74-6691-4929-847d-fd1046a3e3e4/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771025650/Udemy/Wordpress%20for%20Beginners%20-%20Master%20Wordpress%20Quickly.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-07369aee-ca79-4171-93e9-34e04470516c',
    title: 'Complete WordPress Theme & Plugin Development Course',
    provider: 'Udemy',
    issuedAt: 'October 5, 2020',
    verificationUrl: 'https://ude.my/UC-07369aee-ca79-4171-93e9-34e04470516c/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771026411/Udemy/Complete%20WordPress%20Theme%20and%20Plugin%20Development%20Course.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: true
  },
  {
    id: 'udemy-uc-9efa1a23-f8d7-433a-b557-35b49c9d9446',
    title: 'Sexual Harassment Training for Employees in the Workplace',
    provider: 'Udemy',
    issuedAt: 'February 15, 2021',
    verificationUrl: 'https://ude.my/UC-9efa1a23-f8d7-433a-b557-35b49c9d9446/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771025487/Udemy/Sexual%20Harassment%20Training%20for%20Employees%20in%20the%20Workplace.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: false
  },
  {
    id: 'udemy-uc-31095bf9-8230-48c7-9fa4-eb3a855c3931',
    title: 'HTML CSS and SASS Bootcamp',
    provider: 'Udemy',
    issuedAt: 'August 16, 2021',
    verificationUrl: 'https://ude.my/UC-31095bf9-8230-48c7-9fa4-eb3a855c3931/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771028257/Udemy/HTML%20CSS%20and%20SASS%20Bootcamp.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: false
  },
  {
    id: 'udemy-uc-28033c8f-5ed8-4340-9b45-86525eff8448',
    title: 'Learn Jira with real-world examples (+Confluence bonus)',
    provider: 'Udemy',
    issuedAt: 'September 15, 2020',
    verificationUrl: 'https://ude.my/UC-28033c8f-5ed8-4340-9b45-86525eff8448/',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771028460/Udemy/Learn%20Jira%20with%20real-world%20examples%20%28%2BConfluence%20bonus%29.jpg',
    providerIcon: 'simple-icons:udemy',
    featured: false
  },
  {
    id: 'codecademy-a8ab218d5950c29861635cc0bf12fd13',
    title: 'Learn Git & GitHub Course',
    provider: 'Codecademy',
    issuedAt: 'September 22, 2020',
    verificationUrl: 'https://www.codecademy.com/profiles/idmcalculus2020/certificates/a8ab218d5950c29861635cc0bf12fd13',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771038804/Codecademy/Learn%20Git%20and%20GitHub.pdf',
    providerIcon: 'simple-icons:codecademy',
    featured: false
  },
  {
    id: 'codecademy-7ef6f23b56de87623eb4e74e2fca3923',
    title: 'How to Implement Search Algorithms with Python Course',
    provider: 'Codecademy',
    issuedAt: 'April 8, 2022',
    verificationUrl: 'https://www.codecademy.com/profiles/idmcalculus2020/certificates/7ef6f23b56de87623eb4e74e2fca3923',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771038427/Codecademy/How%20to%20Implement%20Search%20Algorithms%20with%20Python%20Course.pdf',
    providerIcon: 'simple-icons:codecademy',
    featured: false
  },
  {
    id: 'codecademy-7ea163c1176d53d69063f6e6386100f1',
    title: 'Learn Intermediate Swift Course',
    provider: 'Codecademy',
    issuedAt: 'January 24, 2022',
    verificationUrl: 'https://www.codecademy.com/profiles/idmcalculus2020/certificates/7ea163c1176d53d69063f6e6386100f1',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771038588/Codecademy/Learn%20Intermediate%20Swift%20Course.pdf',
    providerIcon: 'simple-icons:codecademy',
    featured: false
  },
  {
    id: 'codecademy-18e90daa65479a37c8f909893ada3694',
    title: 'Learn Swift Course',
    provider: 'Codecademy',
    issuedAt: 'January 14, 2022',
    verificationUrl: 'https://www.codecademy.com/profiles/idmcalculus2020/certificates/18e90daa65479a37c8f909893ada3694',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771038992/Codecademy/Learn%20Swift%20Course.pdf',
    providerIcon: 'simple-icons:codecademy',
    featured: false
  },
  {
    id: 'codecademy-c87ba0541f8be78bc2f4ba1128233f6f',
    title: 'Learn the Command Line Course',
    provider: 'Codecademy',
    issuedAt: 'May 8, 2018',
    verificationUrl: 'https://www.codecademy.com/profiles/Idmcalculus/certificates/c87ba0541f8be78bc2f4ba1128233f6f',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771038194/Codecademy/Learn%20the%20Command%20Line%20Course.pdf',
    providerIcon: 'simple-icons:codecademy',
    featured: false
  },
  {
    id: 'openclassrooms-3019520127',
    title: 'Write JavaScript for the Web',
    provider: 'OpenClassrooms',
    issuedAt: 'December 16, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/3019520127',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1771040390/Open%20Classroom%20Certs/Write%20JavaScript%20for%20the%20Web.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-6838694759',
    title: 'Create Simple Prototypes With Wireframes',
    provider: 'OpenClassrooms',
    issuedAt: 'November 25, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/6838694759',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994894/Open%20Classroom%20Certs/Create_simple_prototypes_with_wireframes_arv7sz.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-5680724928',
    title: 'Implement a Relational Database with SQL',
    provider: 'OpenClassrooms',
    issuedAt: 'November 21, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/5680724928',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994896/Open%20Classroom%20Certs/Implement_a_Relational_Database_with_SQL_tssm2y.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-1296143588',
    title: 'Learn About Agile Project Management and Scrum',
    provider: 'OpenClassrooms',
    issuedAt: 'November 19, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/1296143588',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994896/Open%20Classroom%20Certs/Learn_About_Agile_Project_Management_and_SCRUM_q6mavg.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-2459351354',
    title: 'Retrieve Data Using SQL',
    provider: 'OpenClassrooms',
    issuedAt: 'November 21, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/2459351354',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994897/Open%20Classroom%20Certs/Retrieve_data_using_SQL_liqze7.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-8935086465',
    title: 'Secure Your Web Application With OWASP',
    provider: 'OpenClassrooms',
    issuedAt: 'November 7, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/8935086465',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994898/Open%20Classroom%20Certs/Secure_Your_Web_Application_With_OWASP_ov869t.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-5621294917',
    title: 'Create Web Page Layouts With CSS',
    provider: 'OpenClassrooms',
    issuedAt: 'October 5, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/5621294917',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994898/Open%20Classroom%20Certs/Create_Web_Page_Layouts_sfyuhg.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-1690742692',
    title: 'Build Your First Web Pages With HTML and CSS',
    provider: 'OpenClassrooms',
    issuedAt: 'September 13, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/1690742692',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994894/Open%20Classroom%20Certs/Build_Your_First_Web_Pages_sqt3up.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-8956844301',
    title: 'Learn the Command Line in Terminal',
    provider: 'OpenClassrooms',
    issuedAt: 'September 2, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/8956844301',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994896/Open%20Classroom%20Certs/Learn_the_Command_Line_in_Terminal_bzetqx.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-3728143561',
    title: 'Test Your Website’s Interface',
    provider: 'OpenClassrooms',
    issuedAt: 'November 18, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/3728143561',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994898/Open%20Classroom%20Certs/Test_Your_Website_s_Interface_bj8eim.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-5440418070',
    title: 'Go Full-Stack With Node.js, Express, and MongoDB',
    provider: 'OpenClassrooms',
    issuedAt: 'October 27, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/5440418070',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994898/Open%20Classroom%20Certs/Go_Full-Stack_With_Node.js_Express_and_MongoDB_zwdrpq.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-3802356268',
    title: 'Build Your Web Projects With REST APIs',
    provider: 'OpenClassrooms',
    issuedAt: 'October 27, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/3802356268',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994898/Open%20Classroom%20Certs/Build_Your_Web_Projects_With_REST_APIs_vcoxxn.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  },
  {
    id: 'openclassrooms-3834131631',
    title: 'Build web apps with ReactJS',
    provider: 'OpenClassrooms',
    issuedAt: 'October 29, 2019',
    verificationUrl: 'https://openclassrooms.com/en/course-certificates/3834131631',
    imageUrl: 'https://res.cloudinary.com/idmcalculus/image/upload/v1574994894/Open%20Classroom%20Certs/Build_web_apps_with_ReactJS_ppc07u.pdf',
    providerIcon: 'simple-icons:openclassrooms',
    featured: false
  }
]