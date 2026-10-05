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
                <div class="card-header">
                    <form @submit.prevent="getItems()">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="row">
                                    <div class="col-md-4 col-12 mx-auto">
                                        <div
                                            class="flex items-center w-full bg-zinc-200 text-black rounded-full overflow-hidden px-2 py-1"
                                        >
                                            <div class="w-10/12 h-14">
                                                <input
                                                    type="text"
                                                    placeholder="بحث"
                                                    v-model="FormData.search"
                                                    class="input-field focus:outline-none focus:ring-0"
                                                />
                                            </div>

                                            <div
                                                class="w-2/12 flex justify-center items-center"
                                            >
                                                <button
                                                    @click="toggleScanner"
                                                    type="button"
                                                    class="text-zinc-600 transition-all duration-300 hover:text-zinc-800"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="35"
                                                        height="35"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-scan-qr-code-icon lucide-scan-qr-code"
                                                    >
                                                        <path
                                                            d="M17 12v4a1 1 0 0 1-1 1h-4"
                                                        />
                                                        <path
                                                            d="M17 3h2a2 2 0 0 1 2 2v2"
                                                        />
                                                        <path d="M17 8V7" />
                                                        <path
                                                            d="M21 17v2a2 2 0 0 1-2 2h-2"
                                                        />
                                                        <path
                                                            d="M3 7V5a2 2 0 0 1 2-2h2"
                                                        />
                                                        <path d="M7 17h.01" />
                                                        <path
                                                            d="M7 21H5a2 2 0 0 1-2-2v-2"
                                                        />
                                                        <rect
                                                            x="7"
                                                            y="7"
                                                            width="5"
                                                            height="5"
                                                            rx="1"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="error"
                                            class="text-red-600 mt-2"
                                        >
                                            {{ error }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row">
                                    <div
                                        class="col-md-3 col-12 flex justify-center items-center mx-auto"
                                    >
                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                        >
                                            {{ $t("search") }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-11 mx-auto">
                                <div>
                                    <qrcode-stream
                                        v-if="scanning"
                                        @detect="onDetect"
                                        @error="onError"
                                    />
                                </div>

                                <p v-if="result">QR Result: {{ result }}</p>
                            </div>
                        </div>
                    </form>
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
                                    <th scope="col">{{ $t("Code") }}</th>
                                    <th scope="col">{{ $t("national_id") }}</th>
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
                                    <td>{{ item.full_name }}</td>
                                    <td>{{ item.case_code }}</td>
                                    <td>{{ item.national_id }}</td>
                                    <td>
                                        <div
                                            v-if="
                                                !item.is_reviewed &&
                                                !item.is_sent_for_attendance &&
                                                !item.is_attended &&
                                                !item.is_benefited
                                            "
                                        >
                                            {{ $t("Under review") }}
                                        </div>
                                        <div
                                            v-if="
                                                item.is_reviewed &&
                                                !item.is_sent_for_attendance &&
                                                !item.is_attended &&
                                                !item.is_benefited
                                            "
                                        >
                                            {{ $t("Review completed") }}
                                        </div>
                                        <div
                                            v-if="
                                                item.is_reviewed &&
                                                item.is_sent_for_attendance &&
                                                !item.is_attended &&
                                                !item.is_benefited
                                            "
                                        >
                                            {{ $t("Sent for attendance") }}
                                        </div>
                                        <div
                                            v-if="
                                                item.is_reviewed &&
                                                item.is_sent_for_attendance &&
                                                item.is_attended &&
                                                !item.is_benefited
                                            "
                                        >
                                            {{ $t("Attended") }}
                                        </div>
                                        <div
                                            v-if="
                                                item.is_reviewed &&
                                                item.is_sent_for_attendance &&
                                                item.is_attended &&
                                                item.is_benefited
                                            "
                                        >
                                            {{ $t("Benefited") }}
                                        </div>
                                    </td>
                                    <td class="flex gap-2"></td>
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
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import { QrcodeStream } from "vue-qrcode-reader";

export default {
    components: { Bootstrap5Pagination, QrcodeStream },

    data() {
        return {
            items: [],
            currentPage: 1,
            limit: 7,

            result: null,

            scanning: false,
            error: null,

            FormData: {
                code: "",
                national_id: "",
                name: "",
                search: "",
            },

            isLoading: false,
        };
    },

    methods: {
        async getItems(page = this.currentPage) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    `/admin/general-research?page=${page}&general_research=${this.FormData.search}&national_id=${this.FormData.national_id}&code=${this.FormData.code}&name=${this.FormData.name}`
                );
                this.items = data;

                this.currentPage = page;
                this.isLoading = false;
            } catch (error) {
                console.error(error);
            }
        },

        toggleScanner() {
            this.scanning = !this.scanning;
        },

        onDetect(detectedCodes) {
            const qrValue = detectedCodes[0].rawValue;
            this.result = qrValue;
            this.cameraOn = false;
            window.open(this.result, "_blank");
        },

        onError(error) {
            console.error("Camera Error:", error);
            alert("خطأ في فتح الكاميرا");
        },
    },
};
</script>
<style scoped>
.input-field {
    width: 100%;
    height: 100%;
    border: 0;
    background-color: transparent;
    outline: none;
    font-size: 0.875rem; /* text-sm */
}

.input-field::placeholder {
    color: #a1a1aa; /* zinc-400 */
}
</style>
