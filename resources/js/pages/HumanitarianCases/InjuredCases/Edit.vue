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
                                    $t("Injured cases")
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
                                        name: 'admin.humanitarian.cases.injured.index',
                                    }"
                                >
                                    {{ $t("Injured cases") }}
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
                        {{ $t("Edit injured case") }}
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
                            <!-- بيانات المريض الجريح -->
                            <h3 class="section-title">
                                {{ $t("Injured patient data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("full_name") }}</label>
                                    <input
                                        v-model="form.full_name"
                                        :placeholder="$t('full_name')"
                                    />
                                    <div v-if="errors.full_name" class="error">
                                        {{ errors.full_name[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("national_id") }}</label>
                                    <input
                                        v-model="form.national_id"
                                        :placeholder="$t('national_id')"
                                    />
                                    <div
                                        v-if="errors.national_id"
                                        class="error"
                                    >
                                        {{ errors.national_id[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("main_phone") }}</label>
                                    <input
                                        v-model="form.main_phone"
                                        :placeholder="$t('main_phone')"
                                    />
                                    <div v-if="errors.main_phone" class="error">
                                        {{ errors.main_phone[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("additional_phone") }}</label>
                                    <input
                                        v-model="form.additional_phone"
                                        :placeholder="$t('additional_phone')"
                                    />
                                    <div
                                        v-if="errors.additional_phone"
                                        class="error"
                                    >
                                        {{ errors.additional_phone[0] }}
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
                                        class="error"
                                    >
                                        {{ errors.passport_number[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("birth_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.birth_date"
                                        @change="updatePatientAge"
                                    />
                                    <div v-if="errors.birth_date" class="error">
                                        {{ errors.birth_date[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("age") }}</label>
                                    <input
                                        :value="computedPatientAge"
                                        placeholder="Auto calculated"
                                        readonly
                                    />
                                    <div v-if="errors.age" class="error">
                                        {{ errors.age[0] }}
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
                                        style="
                                            width: 100%;
                                            margin-top: 0.25rem;
                                            font-size: 0.875em;
                                            color: #dc3545;
                                            text-align: start;
                                        "
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
                                        style="
                                            width: 100%;
                                            margin-top: 0.25rem;
                                            font-size: 0.875em;
                                            color: #dc3545;
                                            text-align: start;
                                        "
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
                                        style="
                                            width: 100%;
                                            margin-top: 0.25rem;
                                            font-size: 0.875em;
                                            color: #dc3545;
                                            text-align: start;
                                        "
                                    >
                                        {{ errors.area_id[0] }}
                                    </div>
                                </div>

                                <div class="field full-width">
                                    <label>{{ $t("detailed_address") }}</label>
                                    <textarea
                                        v-model="form.detailed_address"
                                        :placeholder="$t('detailed_address')"
                                    ></textarea>
                                    <div
                                        v-if="errors.detailed_address"
                                        class="error"
                                    >
                                        {{ errors.detailed_address[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("housing_status") }}</label>
                                    <select v-model="form.housing_status">
                                        <option value="" disabled>
                                            {{ $t("housing_status") }}
                                        </option>
                                        <option value="Rented">
                                            {{ $t("Rented") }}
                                        </option>
                                        <option value="Owned">
                                            {{ $t("Owned") }}
                                        </option>
                                        <option value="Hosted">
                                            {{ $t("Hosted") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.housing_status"
                                        class="error"
                                    >
                                        {{ errors.housing_status[0] }}
                                    </div>
                                </div>

                                <div
                                    class="field"
                                    v-if="form.housing_status === 'Rented'"
                                >
                                    <label>{{ $t("rent_value") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.rent_value"
                                        :placeholder="$t('rent_value')"
                                    />
                                    <div v-if="errors.rent_value" class="error">
                                        {{ errors.rent_value[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("social_status") }}</label>
                                    <input
                                        v-model="form.social_status"
                                        :placeholder="$t('social_status')"
                                    />
                                    <div
                                        v-if="errors.social_status"
                                        class="error"
                                    >
                                        {{ errors.social_status[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("entry_date_to_egypt")
                                    }}</label>
                                    <input
                                        type="date"
                                        v-model="form.entry_date_to_egypt"
                                    />
                                    <div
                                        v-if="errors.entry_date_to_egypt"
                                        class="error"
                                    >
                                        {{ errors.entry_date_to_egypt[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("entry_method") }}</label>
                                    <select v-model="form.entry_method">
                                        <option value="" disabled>
                                            {{ $t("entry_method") }}
                                        </option>
                                        <option value="Hala Company">
                                            {{ $t("Hala Company") }}
                                        </option>
                                        <option value="External Lists">
                                            {{ $t("External Lists") }}
                                        </option>
                                        <option value="Foreign Lists">
                                            {{ $t("Foreign Lists") }}
                                        </option>
                                        <option value="Medical Referrals">
                                            {{ $t("Medical Referrals") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.entry_method"
                                        class="error"
                                    >
                                        {{ errors.entry_method[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- بيانات الإصابة -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Injury details") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("injury_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.injury_date"
                                    />
                                    <div
                                        v-if="errors.injury_date"
                                        class="error"
                                    >
                                        {{ errors.injury_date[0] }}
                                    </div>
                                </div>

                                <div class="field full-width">
                                    <label>{{ $t("injury_location") }}</label>
                                    <input
                                        v-model="form.injury_location"
                                        :placeholder="$t('injury_location')"
                                    />
                                    <div
                                        v-if="errors.injury_location"
                                        class="error"
                                    >
                                        {{ errors.injury_location[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("injury_caused_disability")
                                    }}</label>
                                    <select
                                        v-model="form.injury_caused_disability"
                                    >
                                        <option value="" disabled>
                                            {{ $t("injury_caused_disability") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.injury_caused_disability"
                                        class="error"
                                    >
                                        {{ errors.injury_caused_disability[0] }}
                                    </div>
                                </div>

                                <div
                                    class="field"
                                    v-if="form.injury_caused_disability"
                                >
                                    <label>{{ $t("disability_type") }}</label>
                                    <select v-model="form.disability_type">
                                        <option value="" disabled>
                                            {{ $t("disability_type") }}
                                        </option>
                                        <option value="physical">
                                            {{ $t("physical") }}
                                        </option>
                                        <option value="mental">
                                            {{ $t("mental") }}
                                        </option>
                                        <option value="both">
                                            {{ $t("both") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.disability_type"
                                        class="error"
                                    >
                                        {{ errors.disability_type[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("received_treatment_from_haq")
                                    }}</label>
                                    <select
                                        v-model="
                                            form.received_treatment_from_haq
                                        "
                                    >
                                        <option value="" disabled>
                                            {{
                                                $t(
                                                    "received_treatment_from_haq",
                                                )
                                            }}
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
                                            errors.received_treatment_from_haq
                                        "
                                        class="error"
                                    >
                                        {{
                                            errors
                                                .received_treatment_from_haq[0]
                                        }}
                                    </div>
                                </div>

                                <div
                                    class="field"
                                    v-if="form.received_treatment_from_haq"
                                >
                                    <label>{{ $t("treatment_type") }}</label>
                                    <input
                                        v-model="form.treatment_type"
                                        :placeholder="$t('treatment_type')"
                                    />
                                    <div
                                        v-if="errors.treatment_type"
                                        class="error"
                                    >
                                        {{ errors.treatment_type[0] }}
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
                                        class="error"
                                    >
                                        {{ errors.health_description[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- المرافق -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Companion data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("has_companion") }}</label>
                                    <select v-model="form.has_companion">
                                        <option value="" disabled>
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
                                        class="error"
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
                                        class="error"
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
                                        class="error"
                                    >
                                        {{ errors.companion_national_id[0] }}
                                    </div>
                                </div>

                                <div class="field" v-if="form.has_companion">
                                    <label>{{
                                        $t("companion_passport_number")
                                    }}</label>
                                    <input
                                        v-model="form.companion_passport_number"
                                        :placeholder="
                                            $t('companion_passport_number')
                                        "
                                    />
                                    <div
                                        v-if="errors.companion_passport_number"
                                        class="error"
                                    >
                                        {{
                                            errors.companion_passport_number[0]
                                        }}
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
                                        class="error"
                                    >
                                        {{ errors.companion_phone[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- الوضع الاقتصادي -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Economic status") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("has_fixed_income") }}</label>
                                    <select v-model="form.has_fixed_income">
                                        <option value="" disabled>
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
                                        class="error"
                                    >
                                        {{ errors.has_fixed_income[0] }}
                                    </div>
                                </div>

                                <div class="field" v-if="form.has_fixed_income">
                                    <label>{{ $t("income_value") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.income_value"
                                        :placeholder="$t('income_value')"
                                    />
                                    <div
                                        v-if="errors.income_value"
                                        class="error"
                                    >
                                        {{ errors.income_value[0] }}
                                    </div>
                                </div>

                                <div class="field" v-if="form.has_fixed_income">
                                    <label>{{ $t("income_source") }}</label>
                                    <input
                                        v-model="form.income_source"
                                        :placeholder="$t('income_source')"
                                    />
                                    <div
                                        v-if="errors.income_source"
                                        class="error"
                                    >
                                        {{ errors.income_source[0] }}
                                    </div>
                                </div>

                                <div class="field full-width">
                                    <label>{{ $t("notes") }}</label>
                                    <textarea
                                        v-model="form.notes"
                                        :placeholder="$t('notes')"
                                    ></textarea>
                                    <div v-if="errors.notes" class="error">
                                        {{ errors.notes[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- أفراد الأسرة -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Family members") }}
                            </h3>
                            <div
                                v-for="(member, idx) in form.family_members"
                                :key="member.id || idx"
                                class="member-card"
                            >
                                <div class="card-header">
                                    <div>
                                        <h4 class="member-title">
                                            {{ $t("member_number") }}
                                            {{ idx + 1 }}
                                        </h4>
                                    </div>
                                    <div class="buttons">
                                        <button
                                            type="button"
                                            class="btn-add"
                                            @click="addFamilyMember"
                                            v-show="
                                                idx ===
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
                                            >
                                                <path d="M5 12h14"></path>
                                                <path d="M12 5v14"></path>
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
                                            >
                                                <path d="M3 6h18"></path>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"
                                                ></path>
                                                <path
                                                    d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                                ></path>
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
                                            class="error"
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
                                            class="error"
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
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.passport_number`
                                                ][0]
                                            }}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label>{{ $t("phone") }}</label>
                                        <input
                                            v-model="member.phone"
                                            :placeholder="$t('phone')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.phone`
                                                ]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `family_members.${idx}.phone`
                                                ][0]
                                            }}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label>{{ $t("birth_date") }}</label>
                                        <input
                                            type="date"
                                            v-model="member.birth_date"
                                            @change="updateFamilyMemberAge(idx)"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `family_members.${idx}.birth_date`
                                                ]
                                            "
                                            class="error"
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
                                            :value="familyMemberAge(idx)"
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
                                            class="error"
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
                                    </div>

                                    <div class="field">
                                        <label>{{ $t("health_status") }}</label>
                                        <input
                                            v-model="member.health_status"
                                            :placeholder="$t('health_status')"
                                        />
                                    </div>

                                    <div class="field">
                                        <label>{{ $t("disease_type") }}</label>
                                        <input
                                            v-model="member.disease_type"
                                            :placeholder="$t('disease_type')"
                                        />
                                    </div>

                                    <div class="field">
                                        <label>{{
                                            $t("has_disability")
                                        }}</label>
                                        <select v-model="member.has_disability">
                                            <option value="" disabled>
                                                {{ $t("has_disability") }}
                                            </option>
                                            <option :value="true">
                                                {{ $t("yes") }}
                                            </option>
                                            <option :value="false">
                                                {{ $t("no") }}
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="field"
                                        v-if="member.has_disability"
                                    >
                                        <label>{{
                                            $t("disability_type")
                                        }}</label>
                                        <select
                                            v-model="member.disability_type"
                                        >
                                            <option value="" disabled>
                                                {{ $t("disability_type") }}
                                            </option>
                                            <option value="physical">
                                                {{ $t("physical") }}
                                            </option>
                                            <option value="mental">
                                                {{ $t("mental") }}
                                            </option>
                                            <option value="both">
                                                {{ $t("both") }}
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="field"
                                        v-if="member.has_disability"
                                    >
                                        <label>{{
                                            $t("disability_description")
                                        }}</label>
                                        <textarea
                                            v-model="
                                                member.disability_description
                                            "
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- مرفقات العضو -->
                                <hr class="divider" />
                                <h4 class="section-title">
                                    {{ $t("Member files") }} {{ idx + 1 }}
                                </h4>
                                <div class="grid_2">
                                    <div>
                                        <label>{{ $t("passport") }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'passport_attachment',
                                                    idx,
                                                )
                                            "
                                        />
                                        <div
                                            v-if="member.existing_passport"
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="member.existing_passport"
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>
                                        <div
                                            v-if="member.passport_attachment"
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        member
                                                            .passport_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            member
                                                                .passport_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFamilyMemberFile(
                                                            'passport_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label>{{ $t("entry_stamp") }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'entry_stamp_attachment',
                                                    idx,
                                                )
                                            "
                                        />
                                        <div
                                            v-if="member.existing_entry_stamp"
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    member.existing_entry_stamp
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>
                                        <div
                                            v-if="member.entry_stamp_attachment"
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        member
                                                            .entry_stamp_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            member
                                                                .entry_stamp_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFamilyMemberFile(
                                                            'entry_stamp_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label>{{
                                            $t("medical_reports")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'medical_reports_attachment',
                                                    idx,
                                                )
                                            "
                                        />
                                        <div
                                            v-if="
                                                member.existing_medical_reports
                                            "
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    member.existing_medical_reports
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>
                                        <div
                                            v-if="
                                                member.medical_reports_attachment
                                            "
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        member
                                                            .medical_reports_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            member
                                                                .medical_reports_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFamilyMemberFile(
                                                            'medical_reports_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- المرفقات الرئيسية -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Required files") }}
                            </h3>
                            <div class="grid_2">
                                <div>
                                    <label>{{ $t("id_attachment") }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'id_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_id"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_id"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.id_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{ form.id_attachment.name }} —
                                                {{
                                                    formatBytes(
                                                        form.id_attachment.size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile('id_attachment')
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label>{{
                                        $t("passport_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'passport_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_passport"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_passport"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.passport_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form.passport_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.passport_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'passport_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label>{{
                                        $t("entry_stamp_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'entry_stamp_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_entry_stamp"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_entry_stamp"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.entry_stamp_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form.entry_stamp_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .entry_stamp_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'entry_stamp_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label>{{
                                        $t("medical_report_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'medical_report_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_medical_report"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_medical_report"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.medical_report_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .medical_report_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .medical_report_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'medical_report_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label>{{
                                        $t("rent_contract_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'rent_contract_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_rent_contract"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_rent_contract"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.rent_contract_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .rent_contract_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .rent_contract_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'rent_contract_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- مرافق إذا موجود -->
                                <div v-if="form.has_companion">
                                    <label>{{
                                        $t("companion_id_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'companion_id_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_companion_id"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="form.existing_companion_id"
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="form.companion_id_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form.companion_id_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .companion_id_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'companion_id_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.has_companion">
                                    <label>{{
                                        $t("companion_passport_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'companion_passport_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_companion_passport"
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="
                                                form.existing_companion_passport
                                            "
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="
                                            form.companion_passport_attachment
                                        "
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .companion_passport_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .companion_passport_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'companion_passport_attachment',
                                                    )
                                                "
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.has_companion">
                                    <label>{{
                                        $t("companion_entry_stamp_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'companion_entry_stamp_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="
                                            form.existing_companion_entry_stamp
                                        "
                                        class="existing-file mt-2"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >{{ $t("Current file") }}:</span
                                        >
                                        <a
                                            :href="
                                                form.existing_companion_entry_stamp
                                            "
                                            target="_blank"
                                            class="ml-2 text-blue-600 underline"
                                        >
                                            {{ $t("View file") }}
                                        </a>
                                    </div>
                                    <div
                                        v-if="
                                            form.companion_entry_stamp_attachment
                                        "
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .companion_entry_stamp_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .companion_entry_stamp_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'companion_entry_stamp_attachment',
                                                    )
                                                "
                                            >
                                                ×
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

            form: {
                full_name: "",
                national_id: "",
                main_phone: "",
                additional_phone: "",
                passport_number: "",
                birth_date: "",
                age: null,
                governorate: "",
                detailed_address: "",
                location: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                entry_date_to_egypt: "",
                entry_method: "",
                injury_date: "",
                injury_location: "",
                injury_caused_disability: false,
                disability_type: "",
                received_treatment_from_haq: false,
                treatment_type: "",
                health_description: "",
                has_companion: false,
                companion_name: "",
                companion_national_id: "",
                companion_passport_number: "",
                companion_phone: "",
                has_fixed_income: false,
                income_value: null,
                income_source: "",
                notes: "",

                // Existing files
                existing_id: null,
                existing_passport: null,
                existing_entry_stamp: null,
                existing_medical_report: null,
                existing_rent_contract: null,
                existing_companion_id: null,
                existing_companion_passport: null,
                existing_companion_entry_stamp: null,

                // New files
                id_attachment: null,
                passport_attachment: null,
                entry_stamp_attachment: null,
                medical_report_attachment: null,
                rent_contract_attachment: null,
                companion_id_attachment: null,
                companion_passport_attachment: null,
                companion_entry_stamp_attachment: null,

                family_members: [],
            },
        };
    },
    async created() {
        await this.fetchCase();
    },
    computed: {
        computedPatientAge() {
            if (!this.form.birth_date) return "";
            const bd = new Date(this.form.birth_date);
            const diff = Date.now() - bd.getTime();
            this.form.age = Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
            return this.form.age;
        },
    },
    methods: {
        async fetchCase() {
            this.isLoading = true;
            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/injured-cases/${this.$route.params.id}`,
                );

                this.form = {
                    ...data.injured_case,

                    // Existing files
                    existing_id: data.injured_case.id_attachment,
                    existing_passport: data.injured_case.passport_attachment,
                    existing_entry_stamp:
                        data.injured_case.entry_stamp_attachment,
                    existing_medical_report:
                        data.injured_case.medical_report_attachment,
                    existing_rent_contract:
                        data.injured_case.rent_contract_attachment,
                    existing_companion_id:
                        data.injured_case.companion_id_attachment,
                    existing_companion_passport:
                        data.injured_case.companion_passport_attachment,
                    existing_companion_entry_stamp:
                        data.injured_case.companion_entry_stamp_attachment,

                    // New files
                    id_attachment: null,
                    passport_attachment: null,
                    entry_stamp_attachment: null,
                    medical_report_attachment: null,
                    rent_contract_attachment: null,
                    companion_id_attachment: null,
                    companion_passport_attachment: null,
                    companion_entry_stamp_attachment: null,

                    family_members: data.injured_case.family_members.map(
                        (m) => ({
                            id: m.id,
                            ...m,
                            existing_passport: m.passport_attachment,
                            existing_entry_stamp: m.entry_stamp_attachment,
                            existing_medical_reports:
                                m.medical_reports_attachment,
                            passport_attachment: null,
                            entry_stamp_attachment: null,
                            medical_reports_attachment: null,
                        }),
                    ),
                };
            } catch (error) {
                this.$swal.fire({
                    icon: "error",
                    title: this.$t("Error"),
                    text: this.$t("Failed to load case data"),
                });
            } finally {
                this.isLoading = false;
            }
        },

        updatePatientAge() {
            this.computedPatientAge;
        },

        async getGovernorates() {
            this.isLoading = true;
            try {
                const { data } = await axios.get("/governorates/get"); // adjust endpoint if needed
                this.governorates = data;
            } catch (error) {
                // ignore silently or show toast
            } finally {
                this.isLoading = false;
            }
        },

        async getCities(id) {
            if (!id) {
                this.cities = [];
                return;
            }
            try {
                const { data } = await axios.get(
                    `/governorates/${id}/cities/get`,
                ); // adjust endpoint
                this.cities = data;
            } catch (error) {
                // ignore
            }
        },

        async getAreas(id) {
            if (!id) {
                this.areas = [];
                return;
            }
            try {
                const { data } = await axios.get(
                    `/governorates/${id}/areas/get`,
                ); // adjust endpoint
                this.areas = data;
            } catch (error) {
                // ignore
            }
        },

        familyMemberAge(idx) {
            const b = this.form.family_members[idx].birth_date;
            if (!b) return "";
            const bd = new Date(b);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },

        updateFamilyMemberAge(idx) {
            this.form.family_members[idx].age = this.familyMemberAge(idx);
        },

        addFamilyMember() {
            this.form.family_members.push({
                name: "",
                national_id: "",
                passport_number: "",
                phone: "",
                birth_date: "",
                age: null,
                relation: "",
                education_level: "",
                health_status: "",
                disease_type: "",
                has_disability: false,
                disability_type: "",
                disability_description: "",
                passport_attachment: null,
                entry_stamp_attachment: null,
                medical_reports_attachment: null,
                existing_passport: null,
                existing_entry_stamp: null,
                existing_medical_reports: null,
            });
        },

        removeFamilyMember(idx) {
            this.form.family_members.splice(idx, 1);
        },

        onFileChange(event, key, index = null) {
            const file = event.target.files[0];
            if (!file) return;
            const maxSize = 700 * 1024;
            if (file.size > maxSize) {
                this.$swal.fire({
                    icon: "error",
                    title: "حجم الملف كبير",
                    text: "حجم الملف لا يتجاوز 700 كيلوبايت",
                });
                event.target.value = "";
                return;
            }
            if (index === null) {
                this.form[key] = file;
            } else {
                this.form.family_members[index][key] = file;
            }
        },

        removeFile(key) {
            this.form[key] = null;
        },

        removeFamilyMemberFile(key, index) {
            this.form.family_members[index][key] = null;
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

        async submit() {
            this.$swal.fire({
                title: this.$t("Updating..."),
                allowOutsideClick: false,
                didOpen: () => this.$swal.showLoading(),
            });

            try {
                let fd = new FormData();

                // الحقول الأساسية
                const mainFields = Object.keys(this.form).filter(
                    (k) =>
                        !k.startsWith("existing_") &&
                        !k.includes("attachment") &&
                        k !== "family_members",
                );
                mainFields.forEach((field) => {
                    fd.append(field, this.form[field] || "");
                });

                // المرفقات الرئيسية (إذا تم رفع ملف جديد)
                const mainAttachments = [
                    "id_attachment",
                    "passport_attachment",
                    "entry_stamp_attachment",
                    "medical_report_attachment",
                    "rent_contract_attachment",
                    "companion_id_attachment",
                    "companion_passport_attachment",
                    "companion_entry_stamp_attachment",
                ];
                mainAttachments.forEach((key) => {
                    if (this.form[key]) fd.append(key, this.form[key]);
                });

                // أفراد الأسرة
                this.form.family_members.forEach((member, i) => {
                    fd.append(`family_members[${i}][id]`, member.id || "");
                    Object.keys(member).forEach((mkey) => {
                        if (!mkey.startsWith("existing_") && mkey !== "id") {
                            let value = member[mkey];
                            if (value instanceof File) {
                                fd.append(
                                    `family_members[${i}][${mkey}]`,
                                    value,
                                );
                            } else if (typeof value === "boolean") {
                                fd.append(
                                    `family_members[${i}][${mkey}]`,
                                    value ? 1 : 0,
                                );
                            } else {
                                fd.append(
                                    `family_members[${i}][${mkey}]`,
                                    value || "",
                                );
                            }
                        }
                    });
                });

                fd.append("_method", "PATCH");
                await axios.post(
                    `/admin/humanitarian-cases/injured-cases/${this.$route.params.id}/update`,
                    fd,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    },
                );

                this.$swal.fire({
                    icon: "success",
                    title: this.$t("Success"),
                    text: this.$t("Case updated successfully"),
                });

                await this.fetchCase();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.$swal.fire({
                        icon: "warning",
                        title: this.$t("Validation Error"),
                        text: this.$t("Please check the fields"),
                    });
                } else {
                    this.$swal.fire({
                        icon: "error",
                        title: this.$t("Error"),
                        text:
                            error.response?.data?.message ||
                            this.$t("Update failed"),
                    });
                }
            } finally {
                this.$swal.close();
            }
        },
    },
};
</script>

<style scoped>
.form-wrapper {
    padding: 15px;
    background: #ffffff;
}
.section-title {
    font-size: 1.125rem;
    font-weight: 800;
    margin-bottom: 12px;
}
.divider {
    margin: 24px 0;
    border: none;
    height: 1px;
    background: #e5e7eb;
}
.grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 16px;
}
.grid_2 {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 16px;
}
@media (min-width: 768px) {
    .grid,
    .grid_2 {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 1024px) {
    .grid {
        grid-template-columns: repeat(3, 1fr);
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
.member-card {
    border: 1px solid #e6e9ee;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
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
    margin-top: 30px;
    text-align: center;
}
.btn-save {
    background: #16a34a;
    color: #fff;
    padding: 12px 24px;
    border: none;
    border-radius: 1rem;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.1rem;
}
.error {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
    text-align: start;
}
.existing-file {
    font-size: 0.875rem;
    color: #6b7280;
}
.file-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    padding: 0.2rem 0.5rem;
    margin: 0.5rem 0;
}
</style>
