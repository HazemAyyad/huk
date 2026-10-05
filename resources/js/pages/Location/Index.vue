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

    <div class="row my-8">
        <div class="col-12">
            <GoogleMaps />
        </div>
    </div>
</template>

<script>
import GoogleMaps from "../../components/GoogleMaps.vue";

export default {
    components: { GoogleMaps },

    data() {
        return {};
    },
};
</script>
