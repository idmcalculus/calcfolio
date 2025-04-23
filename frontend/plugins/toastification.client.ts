import Toast, { type PluginOptions } from 'vue-toastification'
import 'vue-toastification/dist/index.css' // Import the CSS

export default defineNuxtPlugin((nuxtApp) => {
  const options: PluginOptions = {
    // You can set default options here, e.g.:
    // position: "top-right",
    // timeout: 5000,
    // closeOnClick: true,
    // pauseOnFocusLoss: true,
    // pauseOnHover: true,
    // draggable: true,
    // draggablePercent: 0.6,
    // showCloseButtonOnHover: false,
    // hideProgressBar: false,
    // closeButton: "button",
    // icon: true,
    // rtl: false
  };

  nuxtApp.vueApp.use(Toast, options);
});
