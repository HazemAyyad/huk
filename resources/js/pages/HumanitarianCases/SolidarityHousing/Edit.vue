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
                                <router-link
                                    :to="{
                                        name: 'admin.humanitarian.cases.solidarity.housing.index',
                                    }"
                                >
                                    {{ $t("solidarity housing") }}
                                </router-link>
                            </li>
                            <li class="breadcrumb-item active">
                                {{ $t("Edit") }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-4">
        <div class="col-md-11 col-12 mx-auto">
            <div class="card rounded-[1.5rem]">
                <div class="card-header">
                    <h2 class="text-xl font-bold text-center">
                        {{ $t("Edit item") }}
                    </h2>
                </div>
                <div class="card-body">
                    <div
                        v-if="isLoading"
                        class="d-flex justify-content-center p-3"
                    >
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{ $t("Loading...") }}</span>
                        </div>
                    </div>
                    <div v-else class="w-full mx-auto">
                        <form @submit.prevent="submit" class="form-wrapper">
                            <h3 class="section-title">
                                {{ $t("patient_basic_data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("full_name") }}</label>
                                    <input
                                        v-model="form.full_name"
                                        :placeholder="$t('full_name')"
                                    />
                                    <div
                                        v-if="errors.full_name"
                                        class="error-msg"
                                    >
                                        {{ errors.full_name[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("case_code") }}</label>
                                    <input
                                        v-model="form.case_code"
                                        :placeholder="$t('case_code')"
                                        readonly
                                    />
                                </div>
                                <div class="field">
                                    <label>{{ $t("national_id") }}</label>
                                    <input
                                        v-model="form.national_id"
                                        :placeholder="$t('national_id')"
                                    />
                                    <div
                                        v-if="errors.national_id"
                                        class="error-msg"
                                    >
                                        {{ errors.national_id[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("primary_phone") }}</label>
                                    <input
                                        v-model="form.primary_phone"
                                        :placeholder="$t('primary_phone')"
                                    />
                                    <div
                                        v-if="errors.primary_phone"
                                        class="error-msg"
                                    >
                                        {{ errors.primary_phone[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("secondary_phone") }}</label>
                                    <input
                                        v-model="form.secondary_phone"
                                        :placeholder="$t('secondary_phone')"
                                    />
                                    <div
                                        v-if="errors.secondary_phone"
                                        class="error-msg"
                                    >
                                        {{ errors.secondary_phone[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("passport_number") }}</label>
                                    <input
                                        v-model="form.passport_number"
                                        :placeholder="$t('passport_number')"
                                    />
                                    <div
                                        v-if="errors.passport_number"
                                        class="error-msg"
                                    >
                                        {{ errors.passport_number[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("birth_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.birth_date"
                                    />
                                    <div
                                        v-if="errors.birth_date"
                                        class="error-msg"
                                    >
                                        {{ errors.birth_date[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("age") }}</label>
                                    <input :value="computedAge" readonly />
                                </div>
                                <div class="field">
                                    <label>{{ $t("gender") }}</label>
                                    <select v-model="form.gender">
                                        <option value="" disabled selected>
                                            {{ $t("gender") }}
                                        </option>
                                        <option value="male">
                                            {{ $t("male") }}
                                        </option>
                                        <option value="female">
                                            {{ $t("female") }}
                                        </option>
                                    </select>
                                    <div v-if="errors.gender" class="error-msg">
                                        {{ errors.gender[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("governorate") }}</label>
                                    <select
                                        v-model="form.governorate_id"
                                        @change="getCities(form.governorate_id)"
                                    >
                                        <option value="" disabled selected>
                                            {{ $t("governorate") }}
                                        </option>
                                        <option
                                            :value="item.id"
                                            v-for="(
                                                item, index
                                            ) in governorates"
                                            :key="index"
                                        >
                                            {{ item.name }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.governorate_id"
                                        class="error-msg"
                                    >
                                        {{ errors.governorate_id[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("Cities") }}</label>
                                    <select
                                        v-model="form.city_id"
                                        @change="getAreas(form.city_id)"
                                    >
                                        <option value="" disabled selected>
                                            {{ $t("Cities") }}
                                        </option>
                                        <option
                                            :value="item.id"
                                            v-for="(item, index) in cities"
                                            :key="index"
                                        >
                                            {{ item.name }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.city_id"
                                        class="error-msg"
                                    >
                                        {{ errors.city_id[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("areas") }}</label>
                                    <select v-model="form.area_id">
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
                                    <div
                                        v-if="errors.area_id"
                                        class="error-msg"
                                    >
                                        {{ errors.area_id[0] }}
                                    </div>
                                </div>
                                <div class="field full-width">
                                    <label>{{ $t("full_address") }}</label>
                                    <textarea
                                        v-model="form.address"
                                        :placeholder="$t('full_address')"
                                    ></textarea>
                                    <div
                                        v-if="errors.address"
                                        class="error-msg"
                                    >
                                        {{ errors.address[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("housing_status") }}</label>
                                    <select v-model="form.housing_status">
                                        <option value="" disabled selected>
                                            {{ $t("housing_status") }}
                                        </option>
                                        <option value="rent">
                                            {{ $t("rental") }}
                                        </option>
                                        <option value="own">
                                            {{ $t("ownership") }}
                                        </option>
                                        <option value="hosted">
                                            {{ $t("hosted") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.housing_status"
                                        class="error-msg"
                                    >
                                        {{ errors.housing_status[0] }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="form.housing_status == 'rent'"
                                >
                                    <label>{{ $t("rent_value") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.rent_value"
                                        :placeholder="$t('rent_value')"
                                    />
                                    <div
                                        v-if="errors.rent_value"
                                        class="error-msg"
                                    >
                                        {{ errors.rent_value[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("marital_status") }}</label>
                                    <input
                                        v-model="form.marital_status"
                                        :placeholder="$t('marital_status')"
                                    />
                                    <div
                                        v-if="errors.marital_status"
                                        class="error-msg"
                                    >
                                        {{ errors.marital_status[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("health_status") }}</label>
                                    <input
                                        v-model="form.health_status"
                                        :placeholder="$t('health_status')"
                                    />
                                    <div
                                        v-if="errors.health_status"
                                        class="error-msg"
                                    >
                                        {{ errors.health_status[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("entry_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.entry_date"
                                    />
                                    <div
                                        v-if="errors.entry_date"
                                        class="error-msg"
                                    >
                                        {{ errors.entry_date[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("entry_method") }}</label>
                                    <select v-model="form.entry_method">
                                        <option value="" disabled selected>
                                            {{ $t("entry_method") }}
                                        </option>
                                        <option value="hala_company">
                                            {{ $t("hala_company") }}
                                        </option>
                                        <option value="external_lists">
                                            {{ $t("external_lists") }}
                                        </option>
                                        <option value="foreign_lists">
                                            {{ $t("foreign_lists") }}
                                        </option>
                                        <option value="medical_transfers">
                                            {{ $t("medical_transfers") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.entry_method"
                                        class="error-msg"
                                    >
                                        {{ errors.entry_method[0] }}
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("medical_data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("amputation_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.amputation_date"
                                    />
                                    <div
                                        v-if="errors.amputation_date"
                                        class="error-msg"
                                    >
                                        {{ errors.amputation_date[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("amputation_place") }}</label>
                                    <input
                                        v-model="form.amputation_place"
                                        :placeholder="$t('amputation_place')"
                                    />
                                    <div
                                        v-if="errors.amputation_place"
                                        class="error-msg"
                                    >
                                        {{ errors.amputation_place[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("injury_cause") }}</label>
                                    <input
                                        v-model="form.injury_cause"
                                        :placeholder="$t('injury_cause')"
                                    />
                                    <div
                                        v-if="errors.injury_cause"
                                        class="error-msg"
                                    >
                                        {{ errors.injury_cause[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("amputated_part") }}</label>
                                    <input
                                        v-model="form.amputated_part"
                                        :placeholder="$t('amputated_part')"
                                    />
                                    <div
                                        v-if="errors.amputated_part"
                                        class="error-msg"
                                    >
                                        {{ errors.amputated_part[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("needs_prosthetic") }}</label>
                                    <select v-model="form.needs_prosthetic">
                                        <option value="" disabled selected>
                                            {{ $t("needs_prosthetic") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.needs_prosthetic"
                                        class="error-msg"
                                    >
                                        {{ errors.needs_prosthetic[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("received_from_haq") }}</label>
                                    <select
                                        v-model="form.received_aid_from_haq"
                                    >
                                        <option value="" disabled selected>
                                            {{ $t("received_from_haq") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.received_aid_from_haq"
                                        class="error-msg"
                                    >
                                        {{ errors.received_aid_from_haq[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("aid_type") }}</label>
                                    <select v-model="form.aid_type">
                                        <option value="" disabled selected>
                                            {{ $t("aid_type") }}
                                        </option>
                                        <option value="prosthetic">
                                            {{ $t("prosthetic") }}
                                        </option>
                                        <option value="wheelchair">
                                            {{ $t("wheelchair") }}
                                        </option>
                                        <option value="electric_wheelchair">
                                            {{ $t("electric_wheelchair") }}
                                        </option>
                                        <option value="walker">
                                            {{ $t("walker") }}
                                        </option>
                                        <option value="crutch">
                                            {{ $t("crutch") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.aid_type"
                                        class="error-msg"
                                    >
                                        {{ errors.aid_type[0] }}
                                    </div>
                                </div>
                                <div class="field full-width">
                                    <label>{{
                                        $t("health_description")
                                    }}</label>
                                    <textarea
                                        v-model="form.health_description"
                                        :placeholder="$t('health_description')"
                                    ></textarea>
                                    <div
                                        v-if="errors.health_description"
                                        class="error-msg"
                                    >
                                        {{ errors.health_description[0] }}
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("companion_data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("has_companion") }}</label>
                                    <select v-model="form.has_companion">
                                        <option value="" disabled selected>
                                            {{ $t("has_companion") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.has_companion"
                                        class="error-msg"
                                    >
                                        {{ errors.has_companion[0] }}
                                    </div>
                                </div>
                                <div class="field" v-if="form.has_companion">
                                    <label>{{ $t("companion_name") }}</label>
                                    <input
                                        v-model="form.companion_name"
                                        :placeholder="$t('companion_name')"
                                    />
                                    <div
                                        v-if="errors.companion_name"
                                        class="error-msg"
                                    >
                                        {{ errors.companion_name[0] }}
                                    </div>
                                </div>
                                <div class="field" v-if="form.has_companion">
                                    <label>{{
                                        $t("companion_national_id")
                                    }}</label>
                                    <input
                                        v-model="form.companion_national_id"
                                        :placeholder="
                                            $t('companion_national_id')
                                        "
                                    />
                                    <div
                                        v-if="errors.companion_national_id"
                                        class="error-msg"
                                    >
                                        {{ errors.companion_national_id[0] }}
                                    </div>
                                </div>
                                <div class="field" v-if="form.has_companion">
                                    <label>{{ $t("companion_phone") }}</label>
                                    <input
                                        v-model="form.companion_phone"
                                        :placeholder="$t('companion_phone')"
                                    />
                                    <div
                                        v-if="errors.companion_phone"
                                        class="error-msg"
                                    >
                                        {{ errors.companion_phone[0] }}
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("family_economic") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{
                                        $t("family_members_count")
                                    }}</label>
                                    <input
                                        type="number"
                                        v-model.number="
                                            form.family_members_count
                                        "
                                    />
                                    <div
                                        v-if="errors.family_members_count"
                                        class="error-msg"
                                    >
                                        {{ errors.family_members_count[0] }}
                                    </div>
                                </div>
                                <div class="field full-width">
                                    <label>{{
                                        $t("family_composition")
                                    }}</label>
                                    <textarea
                                        v-model="form.family_composition"
                                        :placeholder="$t('family_composition')"
                                    ></textarea>
                                    <div
                                        v-if="errors.family_composition"
                                        class="error-msg"
                                    >
                                        {{ errors.family_composition[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("has_fixed_income") }}</label>
                                    <select v-model="form.has_fixed_income">
                                        <option value="" disabled selected>
                                            {{ $t("has_fixed_income") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.has_fixed_income"
                                        class="error-msg"
                                    >
                                        {{ errors.has_fixed_income[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("monthly_income") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.monthly_income"
                                    />
                                    <div
                                        v-if="errors.monthly_income"
                                        class="error-msg"
                                    >
                                        {{ errors.monthly_income[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("income_source") }}</label>
                                    <input
                                        v-model="form.income_source"
                                        :placeholder="$t('income_source')"
                                    />
                                    <div
                                        v-if="errors.income_source"
                                        class="error-msg"
                                    >
                                        {{ errors.income_source[0] }}
                                    </div>
                                </div>
                                <div class="field full-width">
                                    <label>{{ $t("economic_status") }}</label>
                                    <textarea
                                        v-model="form.economic_status"
                                        :placeholder="$t('economic_status')"
                                    ></textarea>
                                    <div
                                        v-if="errors.economic_status"
                                        class="error-msg"
                                    >
                                        {{ errors.economic_status[0] }}
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("family_members") }}
                            </h3>
                            <div
                                v-for="(member, idx) in form.family_members"
                                :key="idx"
                                class="orphan-card"
                            >
                                <div class="card-header">
                                    <h4 class="orphan-title">
                                        {{ $t("member") }} {{ idx + 1 }}
                                    </h4>
                                    <div class="buttons">
                                        <button
                                            type="button"
                                            class="btn-add"
                                            @click="addFamilyMember"
                                            v-show="
                                                idx ==
                                                form.family_members.length - 1
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M5 12h14" />
                                                <path d="M12 5v14" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-delete"
                                            @click="removeFamilyMember(idx)"
                                            v-show="
                                                form.family_members.length > 1
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                />
                                                <path d="M3 6h18" />
                                                <path
                                                    d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid">
                                    <div class="field">
                                        <label>{{ $t("name") }}</label>
                                        <input
                                            v-model="member.name"
                                            :placeholder="$t('name')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.name`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.name`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("national_id") }}</label>
                                        <input
                                            v-model="member.national_id"
                                            :placeholder="$t('national_id')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.national_id`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.national_id`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("passport_number")
                                        }}</label>
                                        <input
                                            v-model="member.passport_number"
                                            :placeholder="$t('passport_number')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.passport_number`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.passport_number`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("birth_date") }}</label>
                                        <input
                                            type="date"
                                            v-model="member.birth_date"
                                            @change="updateMemberAge(idx)"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.birth_date`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.birth_date`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("age") }}</label>
                                        <input
                                            :value="memberAge(idx)"
                                            readonly
                                        />
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("relation") }}</label>
                                        <input
                                            v-model="member.relation"
                                            :placeholder="$t('relation')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.relation`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.relation`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("education_level")
                                        }}</label>
                                        <input
                                            v-model="member.education_level"
                                            :placeholder="$t('education_level')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.education_level`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.education_level`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("health_status") }}</label>
                                        <input
                                            v-model="member.health_status"
                                            :placeholder="$t('health_status')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.health_status`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.health_status`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("disease_type") }}</label>
                                        <input
                                            v-model="member.disease_type"
                                            :placeholder="$t('disease_type')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.disease_type`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.disease_type`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("has_disability")
                                        }}</label>
                                        <select v-model="member.has_disability">
                                            <option value="" disabled selected>
                                                {{ $t("select_option") }}
                                            </option>
                                            <option :value="true">
                                                {{ $t("yes") }}
                                            </option>
                                            <option :value="false">
                                                {{ $t("no") }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.has_disability`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.has_disability`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="field"
                                        v-if="member.has_disability"
                                    >
                                        <label>{{
                                            $t("disability_type")
                                        }}</label>
                                        <input
                                            v-model="member.disability_type"
                                            :placeholder="$t('disability_type')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.disability_type`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.disability_type`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="field"
                                        v-if="member.has_disability"
                                    >
                                        <label>{{
                                            $t("disability_description")
                                        }}</label>
                                        <input
                                            v-model="
                                                member.disability_description
                                            "
                                            :placeholder="
                                                $t('disability_description')
                                            "
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.disability_description`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.disability_description`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("Required files") }}
                            </h3>
                            <div class="grid_2">
                                <div>
                                    <label>{{ $t("Parent ID") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange($event, 'case_id')
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.case_id"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="existingFiles.case_id"
                                            target="_blank"
                                            >عرض الملف الحالي</a
                                        >
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="removeExisting('case_id')"
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div v-if="form.case_id" class="file-list">
                                        <div class="file-item">
                                            <span>{{
                                                formatBytes(form.case_id.size)
                                            }}</span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="removeFile('case_id')"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label>{{ $t("Parent's passport") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'case_passport',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.case_passport"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="existingFiles.case_passport"
                                            target="_blank"
                                            >عرض الملف الحالي</a
                                        >
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting('case_passport')
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.case_passport"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>{{
                                                formatBytes(
                                                    form.case_passport.size,
                                                )
                                            }}</span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile('case_passport')
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label>{{
                                        $t("Entry stamp for the case")
                                    }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'case_entry_stamp',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.case_entry_stamp"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="
                                                existingFiles.case_entry_stamp
                                            "
                                            target="_blank"
                                            >عرض الملف الحالي</a
                                        >
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'case_entry_stamp',
                                                )
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.case_entry_stamp"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>{{
                                                formatBytes(
                                                    form.case_entry_stamp.size,
                                                )
                                            }}</span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'case_entry_stamp',
                                                    )
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label>{{ $t("Medical reports") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'medical_reports',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.medical_reports"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="
                                                existingFiles.medical_reports
                                            "
                                            target="_blank"
                                            >عرض الملف الحالي</a
                                        >
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'medical_reports',
                                                )
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.medical_reports"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>{{
                                                formatBytes(
                                                    form.medical_reports.size,
                                                )
                                            }}</span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'medical_reports',
                                                    )
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label>{{ $t("lease") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'rent_contract',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.rent_contract"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="existingFiles.rent_contract"
                                            target="_blank"
                                            >عرض الملف الحالي</a
                                        >
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting('rent_contract')
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.rent_contract"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>{{
                                                formatBytes(
                                                    form.rent_contract.size,
                                                )
                                            }}</span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile('rent_contract')
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="24"
                                                    height="24"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="M10 11v6" />
                                                    <path d="M14 11v6" />
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                    />
                                                    <path d="M3 6h18" />
                                                    <path
                                                        d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="footer">
                                <button type="submit" class="btn-save">
                                    {{ $t("update_data") }}
                                </button>
                            </div>
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
            isLoading: true,
            errors: {},
            governorates: [],
            cities: [],
            areas: [],
            existingFiles: {},
            form: {
                case_code: "",
                full_name: "",
                national_id: "",
                primary_phone: "",
                secondary_phone: "",
                passport_number: "",
                birth_date: "",
                gender: "",
                governorate_id: "",
                city_id: "",
                area_id: "",
                address: "",
                housing_status: "",
                rent_value: null,
                marital_status: "",
                health_status: "",
                entry_date: "",
                entry_method: "",
                amputation_date: "",
                amputation_place: "",
                injury_cause: "",
                amputated_part: "",
                needs_prosthetic: null,
                received_aid_from_haq: null,
                aid_type: "",
                health_description: "",
                has_companion: null,
                companion_name: "",
                companion_national_id: "",
                companion_phone: "",
                family_members_count: null,
                family_composition: "",
                has_fixed_income: null,
                monthly_income: null,
                income_source: "",
                economic_status: "",
                case_id: null,
                case_passport: null,
                case_entry_stamp: null,
                medical_reports: null,
                rent_contract: null,
                family_members: [],
            },
        };
    },
    computed: {
        computedAge() {
            if (!this.form.birth_date) return "";
            const bd = new Date(this.form.birth_date);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
    },
    created() {
        this.getGovernorates();
        this.fetchCase();
    },
    methods: {
        async fetchCase() {
            this.isLoading = true;
            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/solidarity-housing/${this.$route.params.id}/edit`,
                );
                const c = data.case;

                this.form = {
                    case_code: c.case_code,
                    full_name: c.full_name,
                    national_id: c.national_id,
                    primary_phone: c.primary_phone,
                    secondary_phone: c.secondary_phone,
                    passport_number: c.passport_number,
                    birth_date: c.birth_date,
                    gender: c.gender,
                    governorate_id: c.governorate_id,
                    city_id: c.city_id,
                    area_id: c.area_id,
                    address: c.address,
                    housing_status: c.housing_status,
                    rent_value: c.rent_value || null,
                    marital_status: c.marital_status,
                    health_status: c.health_status,
                    entry_date: c.entry_date,
                    entry_method: c.entry_method,
                    amputation_date: c.amputation_date,
                    amputation_place: c.amputation_place,
                    injury_cause: c.injury_cause,
                    amputated_part: c.amputated_part,
                    needs_prosthetic: c.needs_prosthetic == 1,
                    received_aid_from_haq: c.received_aid_from_haq == 1,
                    aid_type: c.aid_type,
                    health_description: c.health_description,
                    has_companion: c.has_companion == 1,
                    companion_name: c.companion_name,
                    companion_national_id: c.companion_national_id,
                    companion_phone: c.companion_phone,
                    family_members_count: c.family_members_count || null,
                    family_composition: c.family_composition,
                    has_fixed_income: c.has_fixed_income == 1,
                    monthly_income: c.monthly_income || null,
                    income_source: c.income_source,
                    economic_status: c.economic_status,
                    case_id: null,
                    case_passport: null,
                    case_entry_stamp: null,
                    medical_reports: null,
                    rent_contract: null,
                    family_members: c.family_members.map((m) => ({
                        name: m.name,
                        national_id: m.national_id,
                        passport_number: m.passport_number,
                        birth_date: m.birth_date,
                        relation: m.relation,
                        education_level: m.education_level,
                        health_status: m.health_status,
                        disease_type: m.disease_type,
                        has_disability: m.has_disability == 1,
                        disability_type: m.disability_type,
                        disability_description: m.disability_description,
                    })),
                };

                this.existingFiles = data.files || {};

                if (c.governorate_id) await this.getCities(c.governorate_id);
                if (c.city_id) await this.getAreas(c.city_id);
            } catch (error) {
                console.error(error);
                this.$swal.fire("خطأ", "فشل جلب بيانات الحالة", "error");
            } finally {
                this.isLoading = false;
            }
        },
        async getGovernorates() {
            try {
                const { data } = await axios.get("/governorates");
                this.governorates = data;
            } catch (e) {}
        },
        async getCities(id) {
            if (!id) {
                this.cities = [];
                return;
            }
            try {
                const { data } = await axios.get(`/${id}/cities/get`);
                this.cities = data;
            } catch (e) {}
        },
        async getAreas(id) {
            if (!id) {
                this.areas = [];
                return;
            }
            try {
                const { data } = await axios.get(`/${id}/areas/get`);
                this.areas = data;
            } catch (e) {}
        },
        onFileChange(event, key) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 700 * 1024) {
                this.$swal.fire({
                    icon: "error",
                    title: "خطأ",
                    text: "حجم الملف لا يتجاوز 700 كيلوبايت",
                });
                event.target.value = "";
                return;
            }
            this.form[key] = file;
        },
        removeFile(key) {
            this.form[key] = null;
        },
        removeExisting(key) {
            this.existingFiles[key] = null;
            this.form[`${key}_delete`] = 1;
        },
        formatBytes(bytes, decimals = 2) {
            if (!bytes) return "";
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ["Bytes", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (
                parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) +
                " " +
                sizes[i]
            );
        },
        addFamilyMember() {
            this.form.family_members.push({
                name: "",
                national_id: "",
                passport_number: "",
                birth_date: "",
                relation: "",
                education_level: "",
                health_status: "",
                disease_type: "",
                has_disability: false,
                disability_type: "",
                disability_description: "",
            });
        },
        removeFamilyMember(i) {
            this.form.family_members.splice(i, 1);
        },
        memberAge(idx) {
            const b = this.form.family_members[idx].birth_date;
            if (!b) return "";
            const bd = new Date(b);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        updateMemberAge(idx) {
            // يحسب تلقائيًا
        },
        appendToFormData(formData, data, parentKey = "") {
            for (let key in data) {
                if (!data.hasOwnProperty(key)) continue;
                let value = data[key];
                let fullKey = parentKey ? `${parentKey}[${key}]` : key;

                if (value === true) {
                    formData.append(fullKey, 1);
                } else if (value === false) {
                    formData.append(fullKey, 0);
                } else if (value instanceof File) {
                    formData.append(fullKey, value);
                } else if (Array.isArray(value)) {
                    value.forEach((item, i) => {
                        this.appendToFormData(
                            formData,
                            item,
                            `${fullKey}[${i}]`,
                        );
                    });
                } else if (typeof value === "object" && value !== null) {
                    this.appendToFormData(formData, value, fullKey);
                } else {
                    formData.append(fullKey, value ?? "");
                }
            }
        },
        async submit() {
            this.$swal.fire({
                title: this.$t("Loading..."),
                allowOutsideClick: false,
                didOpen: () => this.$swal.showLoading(),
            });

            try {
                const fd = new FormData();
                this.appendToFormData(fd, this.form);

                // حذف الملفات القديمة
                Object.keys(this.existingFiles).forEach((key) => {
                    if (this.existingFiles[key] === null) {
                        fd.append(`${key}_delete`, 1);
                    }
                });

                fd.append("_method", "PATCH");
                await axios.post(
                    `/${document
                        .getElementsByTagName("html")[0]
                        .getAttribute(
                            "lang",
                        )}/admin/humanitarian-cases/solidarity-housing/${this.$route.params.id}/update`,
                    fd,
                    { headers: { "Content-Type": "multipart/form-data" } },
                );

                this.$swal.fire("تم!", "تم تحديث البيانات بنجاح", "success");
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                }
                this.$swal.fire("خطأ", "حدث خطأ أثناء التحديث", "error");
            }
        },
    },
};
</script>

<style scoped>
.form-wrapper {
    padding: 15px;
    margin: 10px auto;
    background: #ffffff;
    box-sizing: border-box;
}
.section-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 8px;
}
.divider {
    margin: 20px 0;
    border: none;
    height: 1px;
    background: #e5e7eb;
}
.grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 12px;
}
.grid_2 {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 12px;
}
@media (min-width: 768px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .grid_2 {
        grid-template-columns: repeat(2, 1fr);
    }
}
.full-width {
    grid-column: 1 / -1;
}
label {
    display: block;
    margin-top: 6px;
    font-weight: 600;
    font-size: 0.95rem;
    text-align: start;
}
input,
textarea,
select {
    width: 100%;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    margin-top: 6px;
    box-sizing: border-box;
    font-size: 0.95rem;
}
.orphan-card {
    border: 1px solid #e6e9ee;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    background: #fcfcfd;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.buttons {
    display: flex;
    gap: 0.3rem;
}
.btn-add {
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 10px;
    cursor: pointer;
}
.btn-delete {
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 10px;
    cursor: pointer;
}
.footer {
    margin-top: 20px;
    text-align: center;
}
.btn-save {
    background: #16a34a;
    color: #fff;
    padding: 10px 18px;
    border: none;
    border-radius: 1rem;
    cursor: pointer;
    font-weight: 600;
}
.error-msg {
    color: #dc3545;
    margin-top: 6px;
    font-size: 0.875rem;
    text-align: start;
}
.existing-file {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 1rem;
    padding: 0.75rem;
    margin: 0.5rem 0;
}
.existing-file a {
    color: #16a34a;
    text-decoration: underline;
}
.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    padding: 0.5rem;
    margin: 0.5rem 0;
}
</style>
