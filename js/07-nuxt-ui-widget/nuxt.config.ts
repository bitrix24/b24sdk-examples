import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: [
    '@bitrix24/b24ui-nuxt',
    '@nuxt/eslint',
    '@pinia/nuxt',
    '@bitrix24/b24jssdk-nuxt'
  ],
  devtools: { enabled: false },
  devServer: {
    port: 3000
    // host: 'custom.mydomain.local'
    // https: {
    //   key: '.../source/ssl/custom.mydomain.local-key.pem',
    //   cert: '.../source/ssl/custom.mydomain.local.pem'
    // },
  },
  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    /**
     * @memo this will be overwritten from .env
     *
     * @see https://nuxt.com/docs/guide/going-further/runtime-config#example
     */
    public: {
      appUrl: ''
    }
  },
  future: {
    compatibilityVersion: 4
  },

  compatibilityDate: '2025-05-15',

  vite: {
    server: {
      // allow incoming requests from this host
      allowedHosts: [
        'whale-viable-wasp.ngrok-free.app'
      ],
      // and don't forget CORS, if needed:
      cors: true
    },
    plugins: [
      tailwindcss()
    ]
  },

  b24ui: {
    colorMode: false
  }
})
