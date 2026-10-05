<template>
    <div class="row m-1 pb-4 mb-3">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 p-2">
            <div class="page-header breadcrumb-header">
                <div class="row align-items-end">
                    <div class="col-lg-8">
                        <div class="page-header-title text-left-rtl">
                            <div class="d-inline">
                                <h3 class="lite-text">
                                    {{ $t("Humanitarian cases") }}
                                </h3>
                                <span class="lite-text text-gray">{{
                                    $t("Governorates")
                                }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <router-link :to="{ name: 'admin.index' }">
                                    <i class="fas fa-home"></i>
                                </router-link>
                            </li>
                            <li class="breadcrumb-item active">
                                <router-link
                                    :to="{ name: 'admin.governorates.index' }"
                                >
                                    {{ $t("Governorates") }}
                                </router-link>
                            </li>
                            <li class="breadcrumb-item active">
                                {{ $t("Create") }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-4">
        <div class="col-md-7 col-12 mx-auto">
            <div class="card rounded-[1.5rem]">
                <div class="card-header">
                    <h2 class="text-xl font-bold text-center">
                        {{ $t("Create item") }}
                    </h2>
                </div>
                <div class="card-body">
                    <div class="w-11/12 mx-auto">
                        <form @submit.prevent="store">
                            <div class="mb-3">
                                <label for="name">{{ $t("Name") }}</label>
                                <input
                                    type="text"
                                    v-model="FormData.name"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.name }"
                                    id="name"
                                    :placeholder="$t('Name')"
                                />

                                <div
                                    v-if="errors.name"
                                    class="invalid-feedback"
                                >
                                    {{ errors.name[0] }}
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <div class="flex items-center gap-2">
                                    <span>
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-plus-icon lucide-plus"
                                        >
                                            <path d="M5 12h14" />
                                            <path d="M12 5v14" />
                                        </svg>
                                    </span>

                                    <span class="text-[1rem] font-bold">{{
                                        $t("Create Item")
                                    }}</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            FormData: {
                name: "",
            },

            currentLang: document
                .getElementsByTagName("html")[0]
                .getAttribute("lang"),

            errors: {},
        };
    },

    mounted() {},

    methods: {
        async store() {
            this.$swal.fire({
                title: this.$t("Loading..."),
                text: this.$t("Please wait, loading .."),
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    this.$swal.showLoading();
                },
                customClass: {
                    htmlContainer: "swal-text-center",
                },
            });

            try {
                const response = await axios.post(
                    `/${this.currentLang}/admin/governorates/store`,
                    this.FormData,
                );

                this.$router.push({ name: "admin.governorates.index" });
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Success"),
                    text: this.$t("Success"),
                    icon: "success",
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });
            } catch (error) {
                if (error.response) {
                    // السيرفر رد بالخطأ
                    switch (error.response.status) {
                        case 422: // validation error
                            this.errors = error.response.data.errors;

                            console.log("error: ", this.errors.name[0]);
                            this.$swal.fire({
                                allowOutsideClick: false,
                                title: this.$t("Validation Error"),
                                text: this.$t(
                                    "Please check the form and try again",
                                ),
                                icon: "warning",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        case 401: // unauthorized
                            this.$swal.fire({
                                title: this.$t("Unauthorized"),
                                text: this.$t("You are not logged in"),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        case 403: // forbidden
                            this.$swal.fire({
                                title: this.$t("Forbidden"),
                                text: this.$t(
                                    "You don’t have permission to do this",
                                ),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        case 404: // not found
                            this.$swal.fire({
                                title: this.$t("Not Found"),
                                text: this.$t(
                                    "The requested resource was not found",
                                ),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        case 500: // server error
                            this.$swal.fire({
                                allowOutsideClick: false,
                                title: this.$t("Server Error"),
                                text: this.$t(
                                    "Something went wrong, please try again later",
                                ),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        default: // أي Error تاني
                            this.$swal.fire({
                                allowOutsideClick: false,
                                title: this.$t("Error"),
                                text:
                                    error.response.data.message ||
                                    this.$t("Unexpected error occurred"),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                    }
                } else if (error.request) {
                    // مفيش response من السيرفر
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Network Error"),
                        text: this.$t(
                            "No response from the server, please check your connection",
                        ),
                        icon: "error",
                        customClass: {
                            htmlContainer: "swal-text-center",
                        },
                    });
                } else {
                    // خطأ في إعداد الطلب
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Error"),
                        text: error.message,
                        icon: "error",
                        customClass: {
                            htmlContainer: "swal-text-center",
                        },
                    });
                }
            }
        },
    },
};
</script>
