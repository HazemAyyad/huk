import "./bootstrap.js";

import { createApp } from "vue";
import router from "./routes/index.js";
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import { i18nVue } from "laravel-vue-i18n";
import VueGoogleMaps from "@fawmi/vue-google-maps";

/**
 *
 */
import AppComponent from "./pages/App.vue";
import Reviews from "./components/Reviews.vue";
import HumanitarianCaseOrphansCreate from "./components/HumanitarianCase/Orphans/Create.vue";
import HumanitarianCaseAmputationCreate from "./components/HumanitarianCase/Amputation/Create.vue";
import HumanitarianCaseSolidarityHousingCreate from "./components/HumanitarianCase/SolidarityHousing/Create.vue";

import HumanitarianCaseFamiliesCreate from "./components/HumanitarianCase/Families/Create.vue";
import HumanitarianCaseStrandedCreate from "./components/HumanitarianCase/Stranded/Create.vue";
import HumanitarianCaseInjuredCreate from "./components/HumanitarianCase/Injured/Create.vue";
import HumanitarianCasePatientCreate from "./components/HumanitarianCase/Patient/Create.vue";

const app = createApp({});
const googleMapsApiKey = document
    .querySelector('meta[name="google-maps-api-key"]')
    ?.getAttribute("content") ?? "";
let LanguageCurrent = document
    .getElementsByTagName("html")[0]
    .getAttribute("lang");

app.component("app-component", AppComponent);
app.component("reviews-component", Reviews);
app.component(
    "humanitarian-case-orphans-create-component",
    HumanitarianCaseOrphansCreate
);
app.component(
    "humanitarian-case-amputation-create-component",
    HumanitarianCaseAmputationCreate
);
app.component(
    "humanitarian-case-solidarity-create-component",
    HumanitarianCaseSolidarityHousingCreate
);

app.component(
    "humanitarian-case-families-create-component",
    HumanitarianCaseFamiliesCreate
);
app.component(
    "humanitarian-case-stranded-create-component",
    HumanitarianCaseStrandedCreate
);
app.component(
    "humanitarian-case-injured-create-component",
    HumanitarianCaseInjuredCreate
);
app.component(
    "humanitarian-case-patient-create-component",
    HumanitarianCasePatientCreate
);

app.use(i18nVue, {
    lang: LanguageCurrent,
    resolve: (lang) => import(`./lang/${lang}.json`),
})
    .use(router)
    .use(VueGoogleMaps, {
        load: {
            key: googleMapsApiKey,
        },
    })
    .use(VueSweetalert2)
    .mount("#app");
