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
                                    $t("Areas")
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
                                {{ $t("Areas") }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-12">
            <router-link
                :to="{ name: 'admin.areas.create' }"
                class="btn btn-success"
            >
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
                        $t("Create")
                    }}</span>
                </div>
            </router-link>
        </div>
    </div>

    <div class="row my-4">
        <div class="col-12">
            <div class="card rounded-[1.5rem]">
                <div class="card-header">
                    <h2 class="text-xl font-bold mb-4">
                        {{ $t("Areas") }}
                    </h2>
                </div>
                <div class="card-body p-0">
                    <div
                        v-if="isLoading"
                        class="d-flex justify-content-center p-3"
                    >
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{ $t("Loading...") }}</span>
                        </div>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ $t("Name") }}</th>
                                    <th scope="col">{{ $t("City") }}</th>
                                    <th scope="col">{{ $t("Tools") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="areas.data?.length === 0">
                                    <td
                                        colspan="100%"
                                        class="text-center w-100"
                                    >
                                        {{ $t("NO DATA HERE.") }}
                                    </td>
                                </tr>
                                <tr
                                    v-else
                                    v-for="(item, index) in areas.data"
                                    :key="item.id"
                                >
                                    <td scope="row">{{ index + 1 }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.city?.name }}</td>
                                    <td class="flex gap-2">
                                        <router-link
                                            :to="{
                                                name: 'admin.areas.edit',
                                                params: {
                                                    id: item.id,
                                                },
                                            }"
                                            class="btn btn-warning"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    ><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="20"
                                                        height="20"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-pencil-icon lucide-pencil"
                                                    >
                                                        <path
                                                            d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"
                                                        />
                                                        <path d="m15 5 4 4" />
                                                    </svg>
                                                </span>

                                                <span
                                                    class="text-[1rem] font-bold"
                                                    >{{ $t("Edit") }}</span
                                                >
                                            </div>
                                        </router-link>

                                        <button
                                            class="btn btn-danger"
                                            @click="destroyItem(item.id)"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    ><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="20"
                                                        height="20"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-trash-icon lucide-trash"
                                                    >
                                                        <path
                                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                        />
                                                        <path d="M3 6h18" />
                                                        <path
                                                            d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                        /></svg
                                                ></span>

                                                <span
                                                    class="text-[1rem] font-bold"
                                                    >{{ $t("Delete") }}</span
                                                >
                                            </div>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
            areas: [],

            isLoading: true,
        };
    },

    mounted() {
        this.getItems();
    },

    methods: {
        async getItems() {
            try {
                const { data } = await axios.get("/admin/areas/index");
                this.areas = data;
                this.isLoading = false;
            } catch (error) {
                console.error(error);
            }
        },

        async destroyItem(id) {
            try {
                const result = await this.$swal.fire({
                    title: this.$t("Are you sure?"),
                    text: this.$t("You won't be able to revert this!"),
                    icon: "warning",
                    showCancelButton: true,
                    allowOutsideClick: false,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: this.$t("Yes, delete it!"),
                    cancelButtonText: this.$t("Cancel"),
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });

                if (result.isConfirmed) {
                    await axios.delete(`/admin/areas/${id}/destroy`);

                    this.getItems();
                    await this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Deleted!"),
                        text: this.$t("User has been deleted successfully."),
                        icon: "success",
                        customClass: {
                            htmlContainer: "swal-text-center",
                        },
                    });
                }
            } catch (error) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error!"),
                    text: this.$t(
                        "Something went wrong while deleting the user."
                    ),
                    icon: "error",
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });
            }
        },
    },
};
</script>
