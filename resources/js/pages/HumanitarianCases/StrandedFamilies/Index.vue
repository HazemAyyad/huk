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
                    name: 'admin.humanitarian.cases.stranded-families.create',
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
                        {{ $t("stranded-families") }}
                    </h2>
                </div>
                <div class="card-header">
                    <form @submit.prevent="getItems()">
                        <div class="row">
                            <div class="col-md-4 col-12 mb-2">
                                <input
                                    type="text"
                                    class="form-control"
                                    :placeholder="$t('search code')"
                                    v-model="FormData.code"
                                />
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <input
                                    type="text"
                                    class="form-control"
                                    :placeholder="$t('national id')"
                                    v-model="FormData.national_id"
                                />
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <input
                                    type="text"
                                    class="form-control"
                                    :placeholder="$t('search name')"
                                    v-model="FormData.name"
                                />
                            </div>

                            <div class="col-md-4 col-12 mb-2">
                                <select
                                    v-model="FormData.governorate_id"
                                    @change="getCities(FormData.governorate_id)"
                                    class="form-control"
                                >
                                    <option value="" disabled selected>
                                        {{ $t("governorate") }}
                                    </option>
                                    <option
                                        :value="item.id"
                                        v-for="(item, index) in governorates"
                                        :key="index"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <select
                                    v-model="FormData.city_id"
                                    @change="getAreas(FormData.city_id)"
                                    class="form-control"
                                >
                                    <option value="" disabled selected>
                                        {{ $t("cities") }}
                                    </option>
                                    <option
                                        :value="item.id"
                                        v-for="(item, index) in cities"
                                        :key="index"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <select
                                    v-model="FormData.area_id"
                                    class="form-control"
                                >
                                    <option value="" disabled selected>
                                        {{ $t("areas") }}
                                    </option>
                                    <option
                                        :value="item.id"
                                        v-for="(item, index) in areas"
                                        :key="index"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <select
                                    v-model="FormData.status"
                                    class="form-control"
                                >
                                    <option value="" disabled selected>
                                        {{ $t("status") }}
                                    </option>
                                    <option
                                        :value="status.id"
                                        v-for="(status, index) in statuses"
                                        :key="index"
                                    >
                                        {{ status.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="FormData.from"
                                />
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="FormData.to"
                                />
                            </div>
                            <div class="col-md-4 col-12 mb-2">
                                <button type="submit" class="btn btn-success">
                                    {{ $t("search") }}
                                </button>
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
                                    <td class="flex gap-2">
                                        <router-link
                                            :to="{
                                                name: 'admin.humanitarian.cases.stranded-families.edit',
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

                                        <router-link
                                            :to="{
                                                name: 'admin.humanitarian.cases.stranded-families.show',
                                                params: {
                                                    id: item.id,
                                                },
                                            }"
                                            class="btn btn-info btn-flat"
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
                                                    class="lucide lucide-folder-search-icon lucide-folder-search"
                                                >
                                                    <path
                                                        d="M10.7 20H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H20a2 2 0 0 1 2 2v4.1"
                                                    />
                                                    <path d="m21 21-1.9-1.9" />
                                                    <circle
                                                        cx="17"
                                                        cy="17"
                                                        r="3"
                                                    />
                                                </svg>
                                            </div>
                                        </router-link>

                                        <button
                                            class="btn btn-danger btn-flat"
                                            @click="destroyItem(item.id)"
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
                                                    class="lucide lucide-trash-icon lucide-trash"
                                                >
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </div>
                                        </button>

                                        <div class="dropdown">
                                            <button
                                                class="btn btn-secondary dropdown-toggle"
                                                type="button"
                                                id="dropdownMenuButton"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                data-toggle="dropdown"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="0.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="lucide lucide-menu-icon lucide-menu"
                                                >
                                                    <path d="M4 5h16" />
                                                    <path d="M4 12h16" />
                                                    <path d="M4 19h16" />
                                                </svg>
                                            </button>
                                            <div
                                                class="dropdown-menu"
                                                aria-labelledby="dropdownMenuButton"
                                            >
                                                <button
                                                    class="dropdown-item"
                                                    data-target="#sendMessageAttendeesModal"
                                                    @click="setItemId(item.id)"
                                                    data-toggle="modal"
                                                >
                                                    {{
                                                        $t(
                                                            "Send a message to the attendees"
                                                        )
                                                    }}
                                                </button>

                                                <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    data-target="#sendMessageAttendedModal"
                                                    @click="setItemId(item.id)"
                                                    data-toggle="modal"
                                                >
                                                    {{ $t("attended") }}
                                                </button>

                                                <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    @click="setItemId(item.id)"
                                                    data-target="#sendMessageBenefitedModal"
                                                    data-toggle="modal"
                                                >
                                                    {{ $t("benefited") }}
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal w-lg fade"
        id="sendMessageAttendeesModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content card shade">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ $t("Send a message to the attendees") }}
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 space-y-6">
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("message") }}</label
                                >
                                <textarea
                                    v-model="FormAttendee.message"
                                    class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn outlined o-danger c-danger"
                        data-dismiss="modal"
                        id="closeAttendees"
                    >
                        {{ $t("Close") }}
                    </button>
                    <button
                        type="button"
                        :disabled="loading"
                        @click="submitAttendee(itemId)"
                        class="btn outlined f-main"
                    >
                        <span v-if="loading">{{ $t("Saving...") }}</span>
                        <span v-else>{{ $t("Save changes") }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal w-lg fade"
        id="sendMessageAttendedModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content card shade">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ $t("Send a message to the Attended") }}
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 space-y-6">
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("message") }}</label
                                >
                                <textarea
                                    v-model="FormAttended.message"
                                    class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn outlined o-danger c-danger"
                        data-dismiss="modal"
                        id="closeAttended"
                    >
                        {{ $t("Close") }}
                    </button>
                    <button
                        type="button"
                        :disabled="loading"
                        @click="submitAttended(itemId)"
                        class="btn outlined f-main"
                    >
                        <span v-if="loading">{{ $t("Saving...") }}</span>
                        <span v-else>{{ $t("Save changes") }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal w-lg fade"
        id="sendMessageBenefitedModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content card shade">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{ $t("Send a message to the Benefited") }}
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 space-y-6">
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("message") }}</label
                                >
                                <textarea
                                    v-model="FormBenefited.message"
                                    class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn outlined o-danger c-danger"
                        data-dismiss="modal"
                        id="closeBenefited"
                    >
                        {{ $t("Close") }}
                    </button>
                    <button
                        type="button"
                        :disabled="loading"
                        @click="submitBenefited(itemId)"
                        class="btn outlined f-main"
                    >
                        <span v-if="loading">{{ $t("Saving...") }}</span>
                        <span v-else>{{ $t("Save changes") }}</span>
                    </button>
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
            currentPage: 1,

            loading: false,
            openModal: false,

            FormBenefited: {
                message: "",

                latitude: "",
                longitude: "",
            },

            FormAttended: {
                message: "",

                latitude: "",
                longitude: "",
            },

            FormAttendee: {
                message: "",

                latitude: "",
                longitude: "",
            },

            FormData: {
                code: "",
                national_id: "",
                name: "",
                status: "",
                governorate_id: "",
                city_id: "",
                area_id: "",
                from: "",
                to: "",
            },

            governorates: [],
            cities: [],
            areas: [],
            isLoading: true,
        };
    },

    computed: {
        statuses() {
            return [
                { id: 1, name: this.$t("Under review") },
                { id: 2, name: this.$t("Review completed") },
                { id: 3, name: this.$t("Sent for attendance") },
                { id: 4, name: this.$t("Attended") },
                { id: 5, name: this.$t("Benefited") },
            ];
        },
    },

    mounted() {
        this.getItems();
        this.getGovernorates();
    },

    methods: {
        setItemId(id) {
            this.itemId = id;
        },

        async submitAttendee(id) {
            this.$swal.fire({
                title: this.$t("Loading..."),
                text: this.$t("Please wait, loading .."),
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => this.$swal.showLoading(),
                customClass: { htmlContainer: "swal-text-center" },
            });

            if (!navigator.geolocation) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: "Geolocation is not supported by your browser.",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
                return;
            }

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                });

                this.FormAttendee.latitude = position.coords.latitude;
                this.FormAttendee.longitude = position.coords.longitude;

                const response = await axios.patch(
                    `/admin/humanitarian-cases/stranded-families/${id}/sent-attendance`,
                    this.FormAttendee
                );

                document.getElementById("closeAttendees").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.head_full_name}
                رقم الهوية الخاص بكم: ${response.data.case.head_national_id}

                إذا واجهت أي مشكلة في فتح الرابط، برجاء التواصل معنا.
                `;

                const encodedMessage = encodeURIComponent(message);
                const url = `https://wa.me/${phone}?text=${encodedMessage}`;

                window.open(url, "_blank");
            } catch (err) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: err.message || "Something went wrong",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
            }
        },

        async submitAttended(id) {
            this.$swal.fire({
                title: this.$t("Loading..."),
                text: this.$t("Please wait, loading .."),
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => this.$swal.showLoading(),
                customClass: { htmlContainer: "swal-text-center" },
            });

            if (!navigator.geolocation) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: "Geolocation is not supported by your browser.",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
                return;
            }

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                });

                this.FormAttended.latitude = position.coords.latitude;
                this.FormAttended.longitude = position.coords.longitude;

                const response = await axios.patch(
                    `/admin/humanitarian-cases/stranded-families/${id}/attended`,
                    this.FormAttended
                );

                document.getElementById("closeAttended").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.head_full_name}
                رقم الهوية الخاص بكم: ${response.data.case.head_national_id}

                إذا واجهت أي مشكلة في فتح الرابط، برجاء التواصل معنا.
                `;

                const encodedMessage = encodeURIComponent(message);
                const url = `https://wa.me/${phone}?text=${encodedMessage}`;

                window.open(url, "_blank");
            } catch (err) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: err.message || "Something went wrong",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
            }
        },

        async submitBenefited(id) {
            this.$swal.fire({
                title: this.$t("Loading..."),
                text: this.$t("Please wait, loading .."),
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => this.$swal.showLoading(),
                customClass: { htmlContainer: "swal-text-center" },
            });

            if (!navigator.geolocation) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: "Geolocation is not supported by your browser.",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
                return;
            }

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                });

                this.FormBenefited.latitude = position.coords.latitude;
                this.FormBenefited.longitude = position.coords.longitude;

                const response = await axios.patch(
                    `/admin/humanitarian-cases/stranded-families/${id}/benefited`,
                    this.FormBenefited
                );

                document.getElementById("closeBenefited").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.head_full_name}
                رقم الهوية الخاص بكم: ${response.data.case.head_national_id}

                إذا واجهت أي مشكلة في فتح الرابط، برجاء التواصل معنا.
                `;

                const encodedMessage = encodeURIComponent(message);
                const url = `https://wa.me/${phone}?text=${encodedMessage}`;

                window.open(url, "_blank");
            } catch (err) {
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Error"),
                    text: err.message || "Something went wrong",
                    icon: "error",
                    customClass: { htmlContainer: "swal-text-center" },
                });
            }
        },
        async getGovernorates() {
            try {
                const { data } = await axios.get("/governorates/get");
                this.governorates = data;
            } catch (error) {}
        },

        async getCities(id) {
            try {
                const { data } = await axios.get(`/${id}/cities/get`);
                this.cities = data;
            } catch (error) {}
        },

        async getAreas(id) {
            try {
                const { data } = await axios.get(`/${id}/areas/get`);
                this.areas = data;
            } catch (error) {}
        },

        async getItems(page = this.currentPage) {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/stranded-families/index?page=${page}&national_id=${this.FormData.national_id}&code=${this.FormData.code}&name=${this.FormData.name}&governorate=${this.FormData.governorate_id}&city=${this.FormData.city_id}&area=${this.FormData.area_id}&from=${this.FormData.from}&to=${this.FormData.to}&status=${this.FormData.status}`
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
                        `/admin/humanitarian-cases/stranded-families/${id}/destroy`
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
