# Portfolio Frontend

The frontend application for my personal portfolio website, built with Nuxt.js 4 and Vue 3. This modern, responsive portfolio showcases my work as a software engineer with interactive features, smooth animations, and optimized performance.

## 🚀 Features

- **Modern Tech Stack**: Nuxt.js 4, Vue 3, TypeScript, and Tailwind CSS
- **Responsive Design**: Mobile-first approach with adaptive layouts
- **SEO Optimized**: Automatic sitemap generation, meta tags, and Schema.org structured data
- **Performance Focused**: Optimized images, lazy loading, and efficient bundling
- **Interactive Elements**: Smooth animations with AOS, charts with Chart.js
- **Contact Integration**: Contact form with reCAPTCHA v3 protection
- **Admin Dashboard**: Secure admin interface for managing content
- **Accessibility**: WCAG compliant with proper ARIA labels and keyboard navigation

## 🛠️ Tech Stack

- **Framework**: Nuxt.js 4
- **Language**: TypeScript
- **Styling**: Tailwind CSS with PostCSS
- **UI Components**: Nuxt UI (@nuxt/ui)
- **Icons**: Nuxt Icon with Lucide and Simple Icons
- **Animations**: AOS (Animate On Scroll)
- **Charts**: Chart.js with Vue Chart.js
- **Forms**: Vue reCAPTCHA v3 integration
- **SEO**: Nuxt Schema.org, Simple Robots, Simple Sitemap
- **Fonts**: Google Fonts (Manrope)
- **Build Tool**: Vite
- **Package Manager**: Bun (recommended)

## 📦 Installation

### Prerequisites

- Node.js 18+ or Bun
- Backend API running (see backend README)

### Setup

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd frontend
   ```

2. **Install dependencies**:
   ```bash
   # Using Bun (recommended)
   bun install

   # Or using npm
   npm install

   # Or using pnpm
   pnpm install

   # Or using yarn
   yarn install
   ```

3. **Environment Configuration**:
   ```bash
   cp .env.example .env
   ```

   Configure the following environment variables in `.env`:
   ```env
   NUXT_PUBLIC_BACKEND_URL=https://your-backend-api-url
   NUXT_PUBLIC_RECAPTCHA_SITE_KEY=your-recaptcha-site-key
   ```

4. **Prepare Nuxt**:
   ```bash
   bun run postinstall
   ```

## 🚀 Development

### Start Development Server

```bash
# Using Bun
bun run dev

# Or using npm
npm run dev

# Or using pnpm
pnpm dev

# Or using yarn
yarn dev
```

The application will be available at `http://localhost:3000`

### Development Features

- **Hot Module Replacement**: Instant updates during development
- **TypeScript**: Full type checking and IntelliSense support
- **ESLint**: Code linting and formatting
- **Vue DevTools**: Enhanced debugging capabilities

## 🏗️ Build & Deployment

### Production Build

```bash
# Using Bun
bun run build

# Or using npm
npm run build

# Or using pnpm
pnpm build

# Or using yarn
yarn build
```

### Static Site Generation

For static deployment (recommended for portfolio):

```bash
# Using Bun
bun run generate

# Or using npm
npm run generate

# Or using pnpm
pnpm generate

# Or using yarn
yarn generate
```

### Preview Production Build

```bash
# Using Bun
bun run preview

# Or using npm
npm run preview

# Or using pnpm
pnpm preview

# Or using yarn
yarn preview
```

## 📁 Project Structure

```
frontend/
├── assets/              # Static assets (CSS, images)
├── components/          # Vue components
│   ├── HeroSection.vue
│   ├── ProjectCard.vue
│   ├── SkillsSection.vue
│   └── ...
├── composables/         # Vue composables
│   └── useSEO.ts
├── layouts/             # Page layouts
│   └── default.vue
├── pages/               # File-based routing
│   ├── index.vue
│   ├── contact.vue
│   └── projects.vue
├── public/              # Public static files
│   ├── favicon/
│   └── _robots.txt
├── server/              # Server-side code
├── utils/               # Utility functions
├── .env                 # Environment variables
├── nuxt.config.ts       # Nuxt configuration
├── package.json         # Dependencies and scripts
└── tailwind.config.js   # Tailwind CSS configuration
```

## 🔧 Configuration

### Nuxt Configuration

Key configuration options in `nuxt.config.ts`:

- **Modules**: UI components, SEO, image optimization, fonts
- **CSS**: Tailwind CSS and AOS animations
- **Runtime Config**: Backend URL and reCAPTCHA settings
- **SEO**: Meta tags, Schema.org, sitemap generation
- **Build**: TypeScript strict mode, Vite optimization

### Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `NUXT_PUBLIC_BACKEND_URL` | Backend API URL | Yes |
| `NUXT_PUBLIC_RECAPTCHA_SITE_KEY` | Google reCAPTCHA site key | Yes |
| `NUXT_PUBLIC_SITE_URL` | Production site URL (for SEO) | No |

## 🎨 Styling

### Tailwind CSS

The project uses Tailwind CSS v4 with custom configuration:

- **Design System**: Consistent spacing, colors, and typography
- **Dark Mode**: Automatic dark mode support
- **Responsive**: Mobile-first responsive design
- **Custom Components**: Reusable component classes

### Animations

- **AOS**: Scroll-triggered animations
- **CSS Transitions**: Smooth state transitions
- **Vue Transitions**: Component enter/leave animations

## 🔍 SEO & Performance

### SEO Features

- **Meta Tags**: Dynamic meta tags for each page
- **Schema.org**: Structured data for search engines
- **Sitemap**: Automatic XML sitemap generation
- **Robots.txt**: Search engine crawling instructions
- **Canonical URLs**: Prevent duplicate content issues

### Performance Optimizations

- **Image Optimization**: Automatic WebP conversion and lazy loading
- **Code Splitting**: Automatic route-based code splitting
- **Bundle Analysis**: Optimized chunk sizes
- **Caching**: Efficient asset caching strategies

## 🧪 Testing

```bash
# Run tests
bun run test

# Run tests in watch mode
bun run test:watch

# Run tests with coverage
bun run test:coverage
```

## 📱 Pages & Features

### Homepage (`/`)
- Hero section with introduction
- Experience timeline
- Featured projects showcase
- Skills visualization with charts
- Testimonials section
- Download CV functionality

### Projects Page (`/projects`)
- Full project portfolio
- Project filtering and search
- Detailed project cards
- Technology tags
- Project links and demos

### Contact Page (`/contact`)
- Contact form with validation
- reCAPTCHA v3 protection
- Form submission to backend API
- Success/error handling

## 🔐 Security

- **reCAPTCHA v3**: Bot protection for contact forms
- **Input Validation**: Client and server-side validation
- **CORS**: Proper cross-origin resource sharing
- **HTTPS**: SSL/TLS encryption in production

## 🚀 Deployment

### Recommended Deployment Options

1. **Vercel** (Recommended for static sites)
2. **Netlify**
3. **Railway**
4. **GitHub Pages** (with GitHub Actions)

### Deployment Checklist

- [ ] Set production environment variables
- [ ] Configure custom domain
- [ ] Enable HTTPS
- [ ] Set up analytics (optional)
- [ ] Configure monitoring (optional)
- [ ] Test all features in production

## 🤝 Contributing

This is my personal portfolio project. While I don't accept external contributions, feel free to:

- Report bugs or issues
- Suggest improvements
- Use this project as inspiration for your own portfolio

## 📄 License

This project is private and proprietary.

## 📞 Support

If you have questions about this portfolio or would like to discuss potential opportunities, please use the contact form on the website or reach out through:

- **Email**: idm.calculus@gmail.com
- **GitHub**: [@idmcalculus](https://github.com/idmcalculus)
- **LinkedIn**: [idmcalculus](https://linkedin.com/in/idmcalculus)

---

Built with ❤️ by [Damilola Michael Ige](https://buymeacoffee.com/idmcalculus)
