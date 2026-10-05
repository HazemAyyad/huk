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

    <div class="row my-4">
        <div class="col-12">
            <div class="card rounded-[1.5rem]">
                <div class="card-header">
                    <h2 class="text-xl font-bold mb-4">
                        {{ $t("Humanitarian cases") }}
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
                                    <th scope="col">{{ $t("message") }}</th>
                                    <th scope="col">{{ $t("status") }}</th>
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
                                    <td>{{ item.message }}</td>
                                    <td>{{ item.status }}</td>
                                    <td>
                                        <button
                                            class="btn btn-danger btn-flat"
                                            @click="status(item.id)"
                                        >
                                            تغيير الحالة
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
            items: [],

            isLoading: false,
        };
    },

    created() {
        this.getItems();
    },

    methods: {
        async getItems() {
            this.isLoading = true;

            try {
                const { data } = await axios.get(`/admin/reviews/index`);
                this.items = data;

                this.isLoading = false;
            } catch (error) {
                console.error(error);
            }
        },

        async status(id) {
            this.isLoading = true;

            try {
                await axios.patch(`/admin/reviews/${id}/update`);
                this.getItems();
                this.isLoading = false;
            } catch (error) {
                console.error(error);
            }
        },
    },
};
</script>
