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
                                    $t("Users")
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
                                    :to="{ name: 'admin.users.index' }"
                                >
                                    {{ $t("Users") }}
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
                        {{ $t("Create New User") }}
                    </h2>
                </div>
                <div class="card-body">
                    <div
                        v-if="isLoading"
                        class="d-flex justify-content-center p-3"
                    >
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div v-else class="w-11/12 mx-auto">
                        <form @submit.prevent="store">
                            <div class="mb-3">
                                <div
                                    class="position-relative d-inline-block overflow-hidden"
                                >
                                    <img
                                        :src="previewPhoto || photoUrl"
                                        class="rounded-circle w-32 h-32 object-cover border-2 border-zinc-200 shadow"
                                        alt="Profile"
                                    />
                                    <label
                                        class="btn btn-dark position-absolute bottom-0 left-1/2 -translate-x-1/2 rounded-full"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="30"
                                            height="30"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="0.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-upload-icon lucide-upload"
                                        >
                                            <path d="M12 3v12" />
                                            <path d="m17 8-5-5-5 5" />
                                            <path
                                                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                                            />
                                            <use xlink:href="#camera"></use>
                                        </svg>

                                        <input
                                            type="file"
                                            @change="onFileChange"
                                            class="d-none"
                                            accept="image/*"
                                        />
                                    </label>
                                </div>
                            </div>
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

                            <div class="form-group mb-3">
                                <label for="email">{{ $t("Email") }}</label>
                                <input
                                    type="text"
                                    v-model="FormData.email"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.email }"
                                    :placeholder="$t('Email')"
                                    id="email"
                                />

                                <div
                                    v-if="errors.email"
                                    class="invalid-feedback"
                                >
                                    {{ errors.email[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>الراتب</label>
                                <input
                                    type="text"
                                    v-model="FormData.basic_salary"
                                    class="form-control"
                                    :class="{
                                        'is-invalid': errors.basic_salary,
                                    }"
                                    placeholder="الراتب"
                                />

                                <div
                                    v-if="errors.basic_salary"
                                    class="invalid-feedback"
                                >
                                    {{ errors.basic_salary[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password">{{
                                    $t("Password")
                                }}</label>
                                <input
                                    v-model="FormData.password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.password }"
                                    :placeholder="$t('Password')"
                                    id="password"
                                />
                                <div
                                    v-if="errors.password"
                                    class="invalid-feedback"
                                >
                                    {{ errors.password[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="role">{{ $t("Role") }}</label>
                                <select
                                    v-model="FormData.role"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.role }"
                                    id="role"
                                >
                                    <option selected value="">
                                        {{ $t("Role") }}
                                    </option>

                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.name"
                                    >
                                        {{ role.display_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="errors.role"
                                    class="invalid-feedback"
                                >
                                    {{ errors.role[0] }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>{{ $t("Permissions") }}</label>
                                <div
                                    v-for="perm in permissions"
                                    :key="perm.id"
                                    class="flex items-center"
                                >
                                    <input
                                        type="checkbox"
                                        :value="perm.name"
                                        v-model="FormData.permissions"
                                    />
                                    <span class="mx-2">{{
                                        $t(perm.display_name)
                                    }}</span>
                                </div>

                                <div
                                    v-if="errors.permissions"
                                    class="invalid-feedback"
                                >
                                    {{ errors.permissions[0] }}
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
                email: "",
                basic_salary: "",
                password: "",
                role: "",
                permissions: [],
                photo: null,
            },

            previewPhoto: null,
            photoUrl: null,

            roles: [],
            permissions: [],
            errors: {},

            isLoading: true,
        };
    },

    mounted() {
        this.getRolesPermissions();
    },

    methods: {
        onFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.previewPhoto = URL.createObjectURL(file);

                const reader = new FileReader();
                reader.onload = (event) => {
                    this.FormData.photo = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async getRolesPermissions() {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    "/admin/users/roles-permissions"
                );
                this.roles = data.roles;
                this.permissions = data.permissions;
                this.isLoading = false;
            } catch (error) {
                console.error(error);
            }
        },

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
                    "/admin/users/store",
                    this.FormData
                );

                this.$router.push({ name: "admin.users.managers.index" });
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

                            this.$swal.fire({
                                allowOutsideClick: false,
                                title: this.$t("Validation Error"),
                                text: this.$t(
                                    "Please check the form and try again"
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
                                    "You don’t have permission to do this"
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
                                    "The requested resource was not found"
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
                                    "Something went wrong, please try again later"
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
                            "No response from the server, please check your connection"
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
