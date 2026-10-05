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
                                    $t("Humanitarian cases")
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
                                {{ $t("Humanitarian cases") }}
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
                :to="{
                    name: 'admin.humanitarian.cases.assistance-request.create',
                }"
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
                        {{ $t("assistance-request") }}
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
                                    <th scope="col">{{ $t("Code") }}</th>
                                    <th scope="col">
                                        {{ $t("assistance_type") }}
                                    </th>
                                    <th scope="col">
                                        {{ $t("beneficiary_name") }}
                                    </th>
                                    <th scope="col">
                                        {{ $t("beneficiary_nid") }}
                                    </th>
                                    <th scope="col">{{ $t("spouse_name") }}</th>
                                    <th scope="col">{{ $t("birth_date") }}</th>
                                    <th scope="col">
                                        {{ $t("family_members_count") }}
                                    </th>
                                    <th scope="col">{{ $t("Tools") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="items.data?.length === 0">
                                    <td
                                        colspan="100%"
                                        class="text-center w-100"
                                    >
                                        {{ $t("NO DATA HERE.") }}
                                    </td>
                                </tr>
                                <tr
                                    v-else
                                    v-for="(item, index) in items.data"
                                    :key="item.id"
                                >
                                    <td scope="row">{{ index + 1 }}</td>
                                    <td>{{ item.code }}</td>
                                    <td>{{ item.assistance_type }}</td>
                                    <td>{{ item.beneficiary_name }}</td>
                                    <td>{{ item.beneficiary_nid }}</td>
                                    <td>{{ item.spouse_name }}</td>
                                    <td>{{ item.birth_date }}</td>
                                    <td>{{ item.family_members_count }}</td>
                                    <td class="flex gap-2">
                                        <router-link
                                            :to="{
                                                name: 'admin.humanitarian.cases.assistance-request.edit',
                                                params: {
                                                    id: item.id,
                                                },
                                            }"
                                            class="btn btn-warning btn-flat"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="25"
                                                    height="25"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-pencil-icon lucide-pencil"
                                                >
                                                    <path
                                                        d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"
                                                    />
                                                    <path d="m15 5 4 4" />
                                                </svg>
                                            </div>
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <Bootstrap5Pagination
                            :data="items"
                            @pagination-change-page="getItems"
                            :limit.sync="limit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Bootstrap5Pagination } from "laravel-vue-pagination";

export default {
    components: { Bootstrap5Pagination },

    data() {
        return {
            items: [],
            currentPage: 1,
            limit: 7,

            isLoading: true,
        };
    },

    mounted() {
        this.getItems();
    },

    methods: {
        async getItems(page = this.currentPage) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/assistance-request/index?page=${page}`,
                );
                this.items = data;

                this.currentPage = page;
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
                    await axios.delete(
                        `/admin/humanitarian-cases/assistance-request/${id}/destroy`,
                    );

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
                        "Something went wrong while deleting the user.",
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
<style>
.date-input:empty:before {
    content: attr(data-placeholder);
    color: #999;
}
</style>
