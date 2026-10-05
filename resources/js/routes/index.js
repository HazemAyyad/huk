import { createRouter, createWebHistory } from "vue-router";

import HomeIndex from "../pages/Home/Index.vue";
import SearchIndex from "../pages/Search/Index.vue";

import UserIndex from "../pages/Users/Index.vue";
import UserManagerIndex from "../pages/Users/Manager.vue";
import UserCreate from "../pages/Users/Create.vue";
import UserEdit from "../pages/Users/Edit.vue";

import GovernorateIndex from "../pages/Governorates/Index.vue";
import GovernorateCreate from "../pages/Governorates/Create.vue";
import GovernorateEdit from "../pages/Governorates/Edit.vue";

import CityIndex from "../pages/Cities/Index.vue";
import CityCreate from "../pages/Cities/Create.vue";
import CityEdit from "../pages/Cities/Edit.vue";

import AreaIndex from "../pages/Areas/Index.vue";
import AreaCreate from "../pages/Areas/Create.vue";
import AreaEdit from "../pages/Areas/Edit.vue";

import ProgramIndex from "../pages/Programs/Index.vue";
import ProgramCreate from "../pages/Programs/Create.vue";
import ProgramEdit from "../pages/Programs/Edit.vue";

import OrphanIndex from "../pages/HumanitarianCases/Orphans/Index.vue";
import OrphanCreate from "../pages/HumanitarianCases/Orphans/Create.vue";
import OrphanShow from "../pages/HumanitarianCases/Orphans/Show.vue";
import OrphanEdit from "../pages/HumanitarianCases/Orphans/Edit.vue";

import AmputationIndex from "../pages/HumanitarianCases/Amputation/Index.vue";
import AmputationCreate from "../pages/HumanitarianCases/Amputation/Create.vue";
import AmputationShow from "../pages/HumanitarianCases/Amputation/Show.vue";
import AmputationEdit from "../pages/HumanitarianCases/Amputation/Edit.vue";

import SolidarityHousingIndex from "../pages/HumanitarianCases/SolidarityHousing/Index.vue";
import SolidarityHousingCreate from "../pages/HumanitarianCases/SolidarityHousing/Create.vue";
import SolidarityHousingShow from "../pages/HumanitarianCases/SolidarityHousing/Show.vue";
import SolidarityHousingEdit from "../pages/HumanitarianCases/SolidarityHousing/Edit.vue";

/***/
import FamilyBreadwinnersIndex from "../pages/HumanitarianCases/FamilyBreadwinners/Index.vue";
import FamilyBreadwinnersCreate from "../pages/HumanitarianCases/FamilyBreadwinners/Create.vue";
import FamilyBreadwinnersShow from "../pages/HumanitarianCases/FamilyBreadwinners/Show.vue";
import FamilyBreadwinnersEdit from "../pages/HumanitarianCases/FamilyBreadwinners/Edit.vue";

import StrandedFamiliesIndex from "../pages/HumanitarianCases/StrandedFamilies/Index.vue";
import StrandedFamiliesCreate from "../pages/HumanitarianCases/StrandedFamilies/Create.vue";
import StrandedFamiliesShow from "../pages/HumanitarianCases/StrandedFamilies/Show.vue";
import StrandedFamiliesEdit from "../pages/HumanitarianCases/StrandedFamilies/Edit.vue";

import InjuredCasesIndex from "../pages/HumanitarianCases/InjuredCases/Index.vue";
import InjuredCasesCreate from "../pages/HumanitarianCases/InjuredCases/Create.vue";
import InjuredCasesShow from "../pages/HumanitarianCases/InjuredCases/Show.vue";
import InjuredCasesEdit from "../pages/HumanitarianCases/InjuredCases/Edit.vue";

import PatientCasesIndex from "../pages/HumanitarianCases/PatientCases/Index.vue";
import PatientCasesCreate from "../pages/HumanitarianCases/PatientCases/Create.vue";
import PatientCasesShow from "../pages/HumanitarianCases/PatientCases/Show.vue";
import PatientCasesEdit from "../pages/HumanitarianCases/PatientCases/Edit.vue";

import AssistanceRequestIndex from "../pages/HumanitarianCases/AssistanceRequest/Index.vue";
import AssistanceRequestCreate from "../pages/HumanitarianCases/AssistanceRequest/Create.vue";
import AssistanceRequestShow from "../pages/HumanitarianCases/AssistanceRequest/Show.vue";
import AssistanceRequestEdit from "../pages/HumanitarianCases/AssistanceRequest/Edit.vue";

import PaymentIndex from "../pages/EmployeePayments/Index.vue";
import PaymentShow from "../pages/EmployeePayments/Show.vue";

import ReviewIndex from "../pages/Reviews/Index.vue";
import LocationIndex from "../pages/Location/Index.vue";

let LanguageCurrent = document
    .getElementsByTagName("html")[0]
    .getAttribute("lang");

const routes = [
    // Home Routes
    {
        path: `/${LanguageCurrent}/admin/`,
        name: "admin.index",
        component: HomeIndex,
    },

    {
        path: `/${LanguageCurrent}/admin/search`,
        name: "admin.search",
        component: SearchIndex,
    },

    {
        path: `/${LanguageCurrent}/admin/location`,
        name: "admin.location",
        component: LocationIndex,
    },

    /**
     * Users
     */
    {
        path: `/${LanguageCurrent}/admin/users`,
        name: "admin.users.index",
        component: UserIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/users/managers`,
        name: "admin.users.managers.index",
        component: UserManagerIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/users/create`,
        name: "admin.users.create",
        component: UserCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/users/:id/edit`,
        name: "admin.users.edit",
        component: UserEdit,
    },

    /**
     * Governorates
     */
    {
        path: `/${LanguageCurrent}/admin/governorates`,
        name: "admin.governorates.index",
        component: GovernorateIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/governorates/create`,
        name: "admin.governorates.create",
        component: GovernorateCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/governorates/:id/edit`,
        name: "admin.governorates.edit",
        component: GovernorateEdit,
    },

    /**
     * Cities
     */
    {
        path: `/${LanguageCurrent}/admin/cities`,
        name: "admin.cities.index",
        component: CityIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/cities/create`,
        name: "admin.cities.create",
        component: CityCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/cities/:id/edit`,
        name: "admin.cities.edit",
        component: CityEdit,
    },

    /**
     * Areas
     */
    {
        path: `/${LanguageCurrent}/admin/areas`,
        name: "admin.areas.index",
        component: AreaIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/areas/create`,
        name: "admin.areas.create",
        component: AreaCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/areas/:id/edit`,
        name: "admin.areas.edit",
        component: AreaEdit,
    },

    /**
     * Programs
     */
    {
        path: `/${LanguageCurrent}/admin/programs`,
        name: "admin.programs.index",
        component: ProgramIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/programs/create`,
        name: "admin.programs.create",
        component: ProgramCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/programs/:id/edit`,
        name: "admin.programs.edit",
        component: ProgramEdit,
    },

    /**
     *
     *
     */
    {
        path: `/${LanguageCurrent}/admin/employee-payments`,
        name: "admin.payments.index",
        component: PaymentIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/employee-payments/:id/show`,
        name: "admin.payments.show",
        component: PaymentShow,
    },

    /**
     * Humanitarian cases
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/orphans`,
        name: "admin.humanitarian.cases.orphans.index",
        component: OrphanIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/orphans/create`,
        name: "admin.humanitarian.cases.orphans.create",
        component: OrphanCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/orphans/:id/show`,
        name: "admin.humanitarian.cases.orphans.show",
        component: OrphanShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/orphans/:id/edit`,
        name: "admin.humanitarian.cases.orphans.edit",
        component: OrphanEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/amputation`,
        name: "admin.humanitarian.cases.amputation.index",
        component: AmputationIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/amputation/create`,
        name: "admin.humanitarian.cases.amputation.create",
        component: AmputationCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/amputation/:id/show`,
        name: "admin.humanitarian.cases.amputation.show",
        component: AmputationShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/amputation/:id/edit`,
        name: "admin.humanitarian.cases.amputation.edit",
        component: AmputationEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/solidarity-housing`,
        name: "admin.humanitarian.cases.solidarity.housing.index",
        component: SolidarityHousingIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/solidarity-housing/create`,
        name: "admin.humanitarian.cases.solidarity.housing.create",
        component: SolidarityHousingCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/solidarity-housing/:id/show`,
        name: "admin.humanitarian.cases.solidarity.housing.show",
        component: SolidarityHousingShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/solidarity-housing/:id/edit`,
        name: "admin.humanitarian.cases.solidarity.housing.edit",
        component: SolidarityHousingEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/family-breadwinners`,
        name: "admin.humanitarian.cases.family-breadwinners.index",
        component: FamilyBreadwinnersIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/family-breadwinners/create`,
        name: "admin.humanitarian.cases.family-breadwinners.create",
        component: FamilyBreadwinnersCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/family-breadwinners/:id/show`,
        name: "admin.humanitarian.cases.family-breadwinners.show",
        component: FamilyBreadwinnersShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/family-breadwinners/:id/edit`,
        name: "admin.humanitarian.cases.family-breadwinners.edit",
        component: FamilyBreadwinnersEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/stranded-families`,
        name: "admin.humanitarian.cases.stranded-families.index",
        component: StrandedFamiliesIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/stranded-families/create`,
        name: "admin.humanitarian.cases.stranded-families.create",
        component: StrandedFamiliesCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/stranded-families/:id/show`,
        name: "admin.humanitarian.cases.stranded-families.show",
        component: StrandedFamiliesShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/stranded-families/:id/edit`,
        name: "admin.humanitarian.cases.stranded-families.edit",
        component: StrandedFamiliesEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/patient-cases`,
        name: "admin.humanitarian.cases.patient-cases.index",
        component: PatientCasesIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/patient-cases/create`,
        name: "admin.humanitarian.cases.patient-cases.create",
        component: PatientCasesCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/patient-cases/:id/show`,
        name: "admin.humanitarian.cases.patient-cases.show",
        component: PatientCasesShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/patient-cases/:id/edit`,
        name: "admin.humanitarian.cases.patient-cases.edit",
        component: PatientCasesEdit,
    },

    /**
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/injured-cases`,
        name: "admin.humanitarian.cases.injure-cases.index",
        component: InjuredCasesIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/injured-cases/create`,
        name: "admin.humanitarian.cases.injure-cases.create",
        component: InjuredCasesCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/injured-cases/:id/show`,
        name: "admin.humanitarian.cases.injure-cases.show",
        component: InjuredCasesShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/injured-cases/:id/edit`,
        name: "admin.humanitarian.cases.injure-cases.edit",
        component: InjuredCasesEdit,
    },

    /**
     *
     *
     */
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/assistance-request`,
        name: "admin.humanitarian.cases.assistance-request.index",
        component: AssistanceRequestIndex,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/assistance-request/create`,
        name: "admin.humanitarian.cases.assistance-request.create",
        component: AssistanceRequestCreate,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/assistance-request/:id/show`,
        name: "admin.humanitarian.cases.assistance-request.show",
        component: AssistanceRequestShow,
    },
    {
        path: `/${LanguageCurrent}/admin/humanitarian-cases/assistance-request/:id/edit`,
        name: "admin.humanitarian.cases.assistance-request.edit",
        component: AssistanceRequestEdit,
    },

    /**
     *
     *
     */
    {
        path: `/${LanguageCurrent}/admin/reviews`,
        name: "admin.reviews.index",
        component: ReviewIndex,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
