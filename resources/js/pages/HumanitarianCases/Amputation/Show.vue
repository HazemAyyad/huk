<template>
    <div class="container-fluid my-4">
        <!-- 🧭 Header -->
        <div class="page-header breadcrumb-header mb-4">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title text-left-rtl">
                        <div class="d-inline">
                            <h3 class="lite-text">
                                {{ $t("Case Details") }}
                            </h3>
                            <span class="lite-text text-gray">{{
                                $t("View case full details")
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
                            {{ $t("Case Details") }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- ⚙️ Loading Spinner -->
        <div v-if="isLoading" class="d-flex justify-content-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div v-else>
            <div class="row">
                <div class="col-md-12 px-8">
                    <button
                        type="button"
                        data-target="#exampleModal"
                        data-toggle="modal"
                        class="btn flat f-success flex items-center gap-2"
                        v-if="!item.is_reviewed"
                    >
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

                        {{ $t("Researcher's opinion") }}
                    </button>

                    <button
                        class="btn btn-primary btn-flat"
                        v-if="
                            item.is_reviewed &&
                            !item.is_sent_for_attendance &&
                            !item.is_attended &&
                            !item.is_benefited
                        "
                        data-target="#sendMessageAttendeesModal"
                        data-toggle="modal"
                    >
                        {{ $t("Send a message to the attendees") }}
                    </button>

                    <button
                        type="button"
                        class="btn flat f-success flex items-center gap-2"
                        data-target="#sendMessageAttendedModal"
                        data-toggle="modal"
                        v-if="
                            item.is_reviewed &&
                            item.is_sent_for_attendance &&
                            !item.is_attended &&
                            !item.is_benefited
                        "
                    >
                        {{ $t("attended") }}
                    </button>

                    <button
                        type="button"
                        class="btn flat f-success flex items-center gap-2"
                        data-target="#sendMessageBenefitedModal"
                        data-toggle="modal"
                        v-if="
                            item.is_reviewed &&
                            item.is_sent_for_attendance &&
                            item.is_attended &&
                            !item.is_benefited
                        "
                    >
                        {{ $t("benefited") }}
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mx-auto col-12 my-4">
                    <div class="progress-container">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="step"
                            :class="{ active: index < currentStep }"
                        >
                            {{ index + 1 }}

                            <div
                                v-if="index < steps.length - 1"
                                class="line"
                            ></div>
                        </div>
                    </div>
                    <div v-if="currentStep === 1">
                        <h6 class="text-center fw-bold my-4 fs-6">
                            {{ $t("Step 1: Under review") }}
                        </h6>
                    </div>
                    <div v-if="currentStep === 2">
                        <h6 class="text-center fw-bold my-4 fs-6">
                            {{ $t("Step 2: Data Review") }}
                        </h6>
                    </div>

                    <div v-if="currentStep === 3">
                        <h6 class="text-center fw-bold my-4 fs-6">
                            {{ $t("Step 3: He was informed to attend") }}
                        </h6>
                    </div>

                    <div v-if="currentStep === 4">
                        <h6 class="text-center fw-bold my-4 fs-6">
                            {{ $t("Step 4: Attendance completed") }}
                        </h6>
                    </div>

                    <div v-if="currentStep === 5">
                        <h6 class="text-center fw-bold my-4 fs-6">
                            {{ $t("Step 5: Benefited") }}
                        </h6>
                    </div>
                </div>
            </div>

            <div class="jumbotron shade pt-5 mt-2 pb-3">
                <blockquote class="blockquote pt-3 pb-3">
                    <h4 class="c-grey">
                        {{ item.full_name + " | " + item.case_code }}
                    </h4>
                </blockquote>

                <hr class="mt-0 mb-4" />
                <div class="row">
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("National ID") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.national_id || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Phone") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.phone || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Address") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">{{ item.address || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Governorate") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.governorate?.name || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("city") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.city?.name || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Area") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.area?.name || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Housing Status") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.housing_status || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Rent Value") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.rent_value || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Income") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.income || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Entry Date") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.entry_date || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("Researcher Opinion") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.researcher_pinion || "-" }}
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Family Deserves") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.family_deserves || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Reason") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.family_reason || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Medical reports") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            class="btn btn-info"
                            target="_blank"
                            :href="item.medical_reports"
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
                                class="lucide lucide-eye-icon lucide-eye"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("lease") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            class="btn btn-info"
                            target="_blank"
                            :href="item.rent_contract"
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
                                class="lucide lucide-eye-icon lucide-eye"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("Entry stamp for the guardian") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            class="btn btn-info"
                            target="_blank"
                            :href="item.guardian_entry_stamp"
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
                                class="lucide lucide-eye-icon lucide-eye"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Parent's passport") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            class="btn btn-info"
                            target="_blank"
                            :href="item.guardian_passport"
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
                                class="lucide lucide-eye-icon lucide-eye"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("Parent ID") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            class="btn btn-info"
                            target="_blank"
                            :href="item.guardian_id"
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
                                class="lucide lucide-eye-icon lucide-eye"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="modal w-lg fade"
            id="exampleModal"
            tabindex="-1"
            role="dialog"
            aria-labelledby="exampleModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog" role="document">
                <div class="modal-content card shade">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            {{ $t("Enter the researcher's opinion") }}
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
                                        >{{
                                            $t("Condition description")
                                        }}</label
                                    >
                                    <textarea
                                        v-model="form.case_description"
                                        class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                        rows="3"
                                    ></textarea>
                                </div>

                                <div>
                                    <label
                                        class="block text-gray-700 font-medium mb-1"
                                        >{{ $t("Researcher's opinion") }}</label
                                    >
                                    <textarea
                                        v-model="form.researcher_opinion"
                                        class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                        rows="3"
                                    ></textarea>
                                </div>

                                <div>
                                    <label
                                        class="block text-gray-700 font-medium mb-1"
                                        >{{
                                            $t("Is it worth the family?")
                                        }}</label
                                    >
                                    <div class="flex space-x-4">
                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <input
                                                type="radio"
                                                value="yes"
                                                v-model="form.family_deserves"
                                                class="text-blue-600 focus:ring-blue-500"
                                            />
                                            <span>{{ $t("yes") }}</span>
                                        </label>

                                        <label
                                            class="flex items-center space-x-2"
                                        >
                                            <input
                                                type="radio"
                                                value="no"
                                                v-model="form.family_deserves"
                                                class="text-blue-600 focus:ring-blue-500"
                                            />
                                            <span>{{ $t("no") }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-gray-700 font-medium mb-1"
                                        >{{ $t("State the reason") }}</label
                                    >
                                    <textarea
                                        v-model="form.family_reason"
                                        class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-200"
                                        rows="2"
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
                            id="closeResearcher"
                        >
                            {{ $t("Close") }}
                        </button>
                        <button
                            type="button"
                            :disabled="loading"
                            @click="submitForm(item.id)"
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
                            @click="submitAttendee(item.id)"
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
                            @click="submitAttended(item.id)"
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
                            @click="submitBenefited(item.id)"
                            class="btn outlined f-main"
                        >
                            <span v-if="loading">{{ $t("Saving...") }}</span>
                            <span v-else>{{ $t("Save changes") }}</span>
                        </button>
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
            isLoading: true,
            loading: false,
            openModal: false,
            item: {},

            steps: [1, 2, 3, 4, 5],
            currentStep: 1,

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

            form: {
                case_description: "",
                researcher_opinion: "",
                family_deserves: "",
                family_reason: "",
            },
        };
    },
    async mounted() {
        await this.getItem();
    },

    computed: {
        stepPercentage() {
            return (this.currentStep / 4) * 100;
        },
    },

    methods: {
        async getItem() {
            this.isLoading = true;

            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/amputation/${this.$route.params.id}/get-item`
                );
                this.item = data;

                if (
                    !this.item.is_reviewed &&
                    !this.item.is_sent_for_attendance &&
                    !this.item.is_attended &&
                    !this.item.is_benefited
                ) {
                    this.currentStep = 1;
                } else if (
                    this.item.is_reviewed &&
                    !this.item.is_sent_for_attendance &&
                    !this.item.is_attended &&
                    !this.item.is_benefited
                ) {
                    this.currentStep = 2;
                } else if (
                    this.item.is_reviewed &&
                    this.item.is_sent_for_attendance &&
                    !this.item.is_attended &&
                    !this.item.is_benefited
                ) {
                    this.currentStep = 3;
                } else if (
                    this.item.is_reviewed &&
                    this.item.is_sent_for_attendance &&
                    this.item.is_attended &&
                    !this.item.is_benefited
                ) {
                    this.currentStep = 4;
                } else if (
                    this.item.is_reviewed &&
                    this.item.is_sent_for_attendance &&
                    this.item.is_attended &&
                    this.item.is_benefited
                ) {
                    this.currentStep = 5;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoading = false;
            }
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
                    `/admin/humanitarian-cases/amputation/${id}/sent-attendance`,
                    this.FormAttendee
                );
                document.getElementById("closeAttendees").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.primary_phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.full_name}
                رقم الهوية الخاص بكم: ${response.data.case.national_id}

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
                    `/admin/humanitarian-cases/amputation/${id}/attended`,
                    this.FormAttended
                );
                document.getElementById("closeAttended").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.primary_phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.full_name}
                رقم الهوية الخاص بكم: ${response.data.case.national_id}

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
                    `/admin/humanitarian-cases/amputation/${id}/benefited`,
                    this.FormBenefited
                );

                document.getElementById("closeBenefited").click();
                this.$swal.close();

                const phone =
                    "2" + response.data.case.primary_phone?.replace(/\D/g, "");
                const message = `
                ${response.data.message.message.trim()}

                نشكركم على تعاونكم، وهذا هو رابط الـ QR Code الخاص بكم:
                ${response.data.case.qr_code}

                كود الحالة الخاص بكم: ${response.data.case.case_code}
                اسم الحالة الخاص بكم: ${response.data.case.full_name}
                رقم الهوية الخاص بكم: ${response.data.case.national_id}

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

        async submitForm() {
            this.loading = true;
            try {
                await axios.patch(
                    `/admin/humanitarian-cases/amputation/${this.$route.params.id}/researcher-opinion`,
                    this.form
                );
                this.getItem();
                this.loading = false;

                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Success"),
                    text: this.$t("Success"),
                    icon: "success",
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });

                document.getElementById("closeResearcher").click();
            } catch (error) {
                this.loading = false;
                console.error(error);
            }
        },
    },
};
</script>

<style scoped>
.card {
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.progress-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.step {
    width: 40px;
    height: 40px;
    background-color: lightgray;
    color: black;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 18px;
    position: relative;
}

.step.active {
    background-color: #007bff;
    color: white;
}

.line {
    width: 55px;
    height: 5px;
    background-color: lightgray;
    position: absolute;
    top: 50%;
    inset-inline-start: 100%;
    transform: translateY(-50%);
}

.step.active .line {
    background-color: #007bff;
}
</style>
