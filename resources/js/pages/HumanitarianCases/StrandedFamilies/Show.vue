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
                                $t("Stranded family case details")
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
                            {{ $t("Stranded families") }}
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
                    <!-- أزرار الحالة -->
                    <button
                        type="button"
                        data-target="#researcherOpinionModal"
                        data-toggle="modal"
                        class="btn flat f-success flex items-center gap-2 mb-3"
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
                        >
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        {{ $t("Researcher's opinion") }}
                    </button>

                    <button
                        class="btn btn-primary btn-flat mb-3"
                        data-target="#sendMessageAttendeesModal"
                        data-toggle="modal"
                        v-if="
                            item.is_reviewed &&
                            !item.is_sent_for_attendance &&
                            !item.is_attended &&
                            !item.is_benefited
                        "
                    >
                        {{ $t("Send a message to the attendees") }}
                    </button>

                    <button
                        type="button"
                        class="btn flat f-success flex items-center gap-2 mb-3"
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
                        class="btn flat f-success flex items-center gap-2 mb-3"
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

            <!-- تقدم الحالة -->
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
                    <div class="text-center fw-bold my-4 fs-6">
                        <div v-if="currentStep === 1">
                            {{ $t("Step 1: Under review") }}
                        </div>
                        <div v-if="currentStep === 2">
                            {{ $t("Step 2: Data Review") }}
                        </div>
                        <div v-if="currentStep === 3">
                            {{ $t("Step 3: He was informed to attend") }}
                        </div>
                        <div v-if="currentStep === 4">
                            {{ $t("Step 4: Attendance completed") }}
                        </div>
                        <div v-if="currentStep === 5">
                            {{ $t("Step 5: Benefited") }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- بيانات رب الأسرة الرئيسية -->
            <div class="jumbotron shade pt-5 mt-2 pb-3">
                <blockquote class="blockquote pt-3 pb-3">
                    <h4 class="c-grey">
                        {{ item.head_full_name + " | " + item.id }}
                    </h4>
                </blockquote>

                <hr class="mt-0 mb-4" />
                <div class="row">
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_national_id") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_national_id || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_main_phone") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_main_phone || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_additional_phone") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_additional_phone || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_passport_number") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_passport_number || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_birth_date") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_birth_date || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_age") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.head_age || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("governorate") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.governorate || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("detailed_address") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.detailed_address || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("location") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <a
                            v-if="item.location"
                            :href="item.location"
                            target="_blank"
                            class="btn btn-info"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("housing_status") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t(item.housing_status) || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("rent_value") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.rent_value || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("social_status") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.social_status || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_health_status") }}:
                        </h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_health_status || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_is_employed") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.head_is_employed ? $t("yes") : $t("no") }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_job") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.head_job || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("head_income") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.head_income || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("entry_date_to_egypt") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.entry_date_to_egypt || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("entry_method") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t(item.entry_method) || "-" }}
                        </h6>
                    </div>

                    <!-- رأي الباحث والإدارة -->
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("case_description") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.case_description || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("researcher_opinion") }}:
                        </h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.researcher_opinion || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("deserves_support") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.deserves_support ? $t("yes") : $t("no") }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("deserve_reason") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.deserve_reason || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("researcher_name") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.researcher_name || "-" }}
                        </h6>
                    </div>
                </div>
            </div>

            <!-- بيانات الزوجة -->
            <div class="jumbotron shade pt-5 mt-2 pb-3">
                <blockquote class="blockquote pt-3 pb-3">
                    <h4 class="c-grey">{{ $t("Wife data") }}</h4>
                </blockquote>
                <hr class="mt-0 mb-4" />
                <div class="row">
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("wife_name") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.wife_name || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("wife_phone") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ item.wife_phone || "-" }}</h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("wife_national_id") }}:</h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.wife_national_id || "-" }}
                        </h6>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">{{ $t("economic_notes") }}:</h6>
                    </div>
                    <div class="col-md-9 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ item.economic_notes || "-" }}
                        </h6>
                    </div>
                </div>
            </div>

            <!-- مرفقات رب الأسرة والزوجة -->
            <div class="jumbotron shade pt-5 mt-2 pb-3">
                <blockquote class="blockquote pt-3 pb-3">
                    <h4 class="c-grey">{{ $t("Required files") }}</h4>
                </blockquote>
                <hr class="mt-0 mb-4" />
                <div class="row">
                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_id_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.head_id_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.head_id_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_passport_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.head_passport_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.head_passport_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_entry_stamp_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.head_entry_stamp_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.head_entry_stamp_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("head_medical_reports_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.head_medical_reports_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.head_medical_reports_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("rent_contract_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.rent_contract_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.rent_contract_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("wife_id_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.wife_id_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.wife_id_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("wife_passport_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.wife_passport_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.wife_passport_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>

                    <div class="col-md-3 col-12 p-2">
                        <h6 class="fnt-code">
                            {{ $t("wife_entry_stamp_attachment") }}:
                        </h6>
                    </div>
                    <div class="col-md-3 col-12 p-2">
                        <a
                            v-if="item.wife_entry_stamp_attachment"
                            class="btn btn-info"
                            target="_blank"
                            :href="item.wife_entry_stamp_attachment"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="25"
                                height="25"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            >
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>
                        <span v-else>-</span>
                    </div>
                </div>
            </div>

            <!-- أفراد الأسرة -->
            <div class="row">
                <div class="col-12">
                    <h4 class="fnt-md fnt-code">{{ $t("Family members") }}</h4>
                </div>

                <div
                    v-if="!item.members || item.members.length === 0"
                    class="text-center fnt-sm p-3"
                >
                    {{ $t("No family members registered for this case.") }}
                </div>

                <div
                    v-else
                    v-for="member in item.members"
                    :key="member.id"
                    class="col-md-6 col-sm-12 p-2"
                >
                    <div class="card shade c-grey">
                        <h5 class="card-header c-main fnt-sm">
                            {{ member.name }}
                        </h5>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("relation") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.relation || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("national_id") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.national_id || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">{{ $t("phone") }}:</h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.phone || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("passport_number") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.passport_number || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("birth_date") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.birth_date || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">{{ $t("age") }}:</h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.age || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("education_level") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.education_level || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("health_status") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.health_status || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("disease_type") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ member.disease_type || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("has_disability") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{
                                            member.has_disability
                                                ? $t("yes")
                                                : $t("no")
                                        }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("disability_type") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t(member.disability_type) || "-" }}
                                    </h6>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("disability_description") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{
                                            member.disability_description || "-"
                                        }}
                                    </h6>
                                </div>

                                <!-- مرفقات العضو -->
                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("passport") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <a
                                        v-if="member.passport_attachment"
                                        class="btn btn-info"
                                        target="_blank"
                                        :href="member.passport_attachment"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="25"
                                            height="25"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1"
                                        >
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                            />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <span v-else>-</span>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("entry_stamp") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <a
                                        v-if="member.entry_stamp_attachment"
                                        class="btn btn-info"
                                        target="_blank"
                                        :href="member.entry_stamp_attachment"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="25"
                                            height="25"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1"
                                        >
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                            />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <span v-else>-</span>
                                </div>

                                <div class="col-md-6 col-12 p-2">
                                    <h6 class="fnt-code">
                                        {{ $t("medical_reports") }}:
                                    </h6>
                                </div>
                                <div class="col-md-6 col-12 p-2">
                                    <a
                                        v-if="member.medical_reports_attachment"
                                        class="btn btn-info"
                                        target="_blank"
                                        :href="
                                            member.medical_reports_attachment
                                        "
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="25"
                                            height="25"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1"
                                        >
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"
                                            />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <span v-else>-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- مودال رأي الباحث -->
        <div
            class="modal w-lg fade"
            id="researcherOpinionModal"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog" role="document">
                <div class="modal-content card shade">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $t("Enter the researcher's opinion") }}
                        </h5>
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                        >
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("case_description") }}</label
                                >
                                <textarea
                                    v-model="form.case_description"
                                    class="w-full border rounded-lg p-2"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("researcher_opinion") }}</label
                                >
                                <textarea
                                    v-model="form.researcher_opinion"
                                    class="w-full border rounded-lg p-2"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("deserves_support") }}</label
                                >
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            value="true"
                                            v-model="form.deserves_support"
                                        />
                                        <span class="mr-2">{{
                                            $t("yes")
                                        }}</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            type="radio"
                                            value="false"
                                            v-model="form.deserves_support"
                                        />
                                        <span class="mr-2">{{ $t("no") }}</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 font-medium mb-1"
                                    >{{ $t("deserve_reason") }}</label
                                >
                                <textarea
                                    v-model="form.deserve_reason"
                                    class="w-full border rounded-lg p-2"
                                    rows="2"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn outlined o-danger c-danger"
                            data-dismiss="modal"
                        >
                            {{ $t("Close") }}
                        </button>
                        <button
                            type="button"
                            @click="submitResearcherOpinion"
                            class="btn outlined f-main"
                        >
                            {{ $t("Save changes") }}
                        </button>
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
            item: {},
            steps: [1, 2, 3, 4, 5],
            currentStep: 1,
            form: {
                case_description: "",
                researcher_opinion: "",
                deserves_support: "",
                deserve_reason: "",
            },
            // نماذج الرسائل
            FormAttendee: { message: "", latitude: "", longitude: "" },
            FormAttended: { message: "", latitude: "", longitude: "" },
            FormBenefited: { message: "", latitude: "", longitude: "" },
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
                    `/admin/humanitarian-cases/stranded-families/${this.$route.params.id}/get-item`
                );
                this.item = data;

                // تحديد خطوة التقدم
                if (!this.item.is_reviewed) this.currentStep = 1;
                else if (
                    this.item.is_reviewed &&
                    !this.item.is_sent_for_attendance
                )
                    this.currentStep = 2;
                else if (
                    this.item.is_sent_for_attendance &&
                    !this.item.is_attended
                )
                    this.currentStep = 3;
                else if (this.item.is_attended && !this.item.is_benefited)
                    this.currentStep = 4;
                else this.currentStep = 5;
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

        async submitForm() {
            this.loading = true;

            try {
                await axios.patch(
                    `/admin/humanitarian-cases/stranded-families/${this.$route.params.id}/researcher-opinion`,
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

        // دوال المودالات (submitResearcherOpinion, submitAttendee, submitAttended, submitBenefited)
        // نفس الكود في صفحة الأيتام مع تغيير المسار إلى stranded-families
    },
};
</script>

<style scoped>
/* نفس الستايل المستخدم في صفحة عرض حالات الأيتام */
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
.card {
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}
</style>
