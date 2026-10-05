<template>
    <div class="row m-1 pb-4 mb-3">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 p-2">
            <div class="page-header breadcrumb-header">
                <div class="row align-items-end">
                    <div class="col-lg-8">
                        <div class="page-header-title text-left-rtl">
                            <div class="d-inline">
                                <h3 class="lite-text">
                                    {{ $t("Dashboard") }}
                                </h3>
                                <span class="lite-text text-gray">{{
                                    $t("Report and analytics")
                                }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <router-link
                                    :to="{
                                        name: 'admin.index',
                                    }"
                                >
                                    <i class="fas fa-home"></i>
                                </router-link>
                            </li>
                            <li class="breadcrumb-item active">
                                {{ $t("Dashboard") }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row m-1 mb-2">
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/orphan.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-first">{{ $t("Orphans") }}</span>
                <span>{{ analysis.orphans }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link to="/admin/orphans">
                        {{ $t("Orphans") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/orphan.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-first">{{ $t("Orphans") }}</span>
                <span>{{ analysis.orphans }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link to="/admin/orphans">
                        {{ $t("Orphans") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/amputation.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-second">{{ $t("amputation") }}</span>
                <span>{{ analysis.amputations }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.amputation.index',
                        }"
                        class=""
                    >
                        {{ $t("amputation") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/solidarity_housing.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("solidarity housing") }}</span>
                <span>{{ analysis.solidarityHousing }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.solidarity.housing.index',
                        }"
                        class=""
                    >
                        {{ $t("solidarity housing") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/family_breadwinner.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("family-breadwinners") }}</span>
                <span>{{ analysis.familyBreadwinner }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.family-breadwinners.index',
                        }"
                        class=""
                    >
                        {{ $t("family-breadwinners") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/stranded_family.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("stranded-families") }}</span>
                <span>{{ analysis.strandedFamily }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.stranded-families.index',
                        }"
                        class=""
                    >
                        {{ $t("stranded-families") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/injured_case.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("injure-cases") }}</span>
                <span>{{ analysis.injuredCase }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.injure-cases.index',
                        }"
                        class=""
                    >
                        {{ $t("injure-cases") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/patient_case.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("patient-cases") }}</span>
                <span>{{ analysis.patientCase }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.patient-cases.index',
                        }"
                        class=""
                    >
                        {{ $t("patient-cases") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/orphan.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("assistance-request") }}</span>
                <span>{{ analysis.assistanceRequest }}</span>
                <p class="mt-3 mb-1 text-center">
                    <router-link
                        :to="{
                            name: 'admin.humanitarian.cases.assistance-request.index',
                        }"
                        class=""
                    >
                        {{ $t("assistance-request") }}
                    </router-link>
                </p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-sm-6 p-2">
            <div class="box-card mini animate__animated animate__flipInY">
                <i class="" aria-hidden="true">
                    <img src="/files/orphan.png" alt="orphan" class="w-full"></img>
                </i>
                <span class="c-third">{{ $t("Total") }}</span>
                <span>{{ analysis.total }}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            analysis: {},
        };
    },

    created() {
        this.getAnalysis();
    },

    methods: {
        getAnalysis() {
            axios
                .get(`/admin/get-analysis`)
                .then((response) => {
                    this.analysis = response.data;
                })
                .catch((error) => {});
        },
    },
};
</script>
