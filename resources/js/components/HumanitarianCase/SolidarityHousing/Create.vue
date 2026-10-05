<template>
    <div>
        <div
            v-if="isLoading"
            style="display: flex; justify-content: center; padding: 1rem"
        >
            <div
                style="
                    width: 2rem;
                    height: 2rem;
                    border: 0.25em solid currentColor;
                    border-right-color: transparent;
                    border-radius: 50%;
                    animation: spin__ 0.75s linear infinite;
                "
            ></div>
        </div>

        <form v-else @submit.prevent="submit" class="form-wrapper">
            <h3 class="section-title">
                {{ $t ? $t("patient_basic_data") : "Patient Basic Data" }}
            </h3>
            <div class="grid">
                <div class="field">
                    <label>{{ $t ? $t("full_name") : "Full Name" }}</label>
                    <input
                        v-model="form.full_name"
                        :placeholder="$t ? $t('full_name') : ''"
                    />
                    <div v-if="errors.full_name" class="error-msg">
                        {{ errors.full_name[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t ? $t("national_id") : "National ID" }}</label>
                    <input
                        v-model="form.national_id"
                        :placeholder="$t ? $t('national_id') : ''"
                    />
                    <div v-if="errors.national_id" class="error-msg">
                        {{ errors.national_id[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("primary_phone") : "Primary Phone"
                    }}</label>
                    <input
                        v-model="form.primary_phone"
                        :placeholder="$t ? $t('primary_phone') : ''"
                    />
                    <div v-if="errors.primary_phone" class="error-msg">
                        {{ errors.primary_phone[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("secondary_phone") : "Secondary Phone"
                    }}</label>
                    <input
                        v-model="form.secondary_phone"
                        :placeholder="$t ? $t('secondary_phone') : ''"
                    />
                    <div v-if="errors.secondary_phone" class="error-msg">
                        {{ errors.secondary_phone[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("passport_number") : "Passport Number"
                    }}</label>
                    <input
                        v-model="form.passport_number"
                        :placeholder="$t ? $t('passport_number') : ''"
                    />
                    <div v-if="errors.passport_number" class="error-msg">
                        {{ errors.passport_number[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t ? $t("birth_date") : "Birth Date" }}</label>
                    <input type="date" v-model="form.birth_date" />
                    <div v-if="errors.birth_date" class="error-msg">
                        {{ errors.birth_date[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t ? $t("age") : "Age" }}</label>
                    <input :value="computedAge" readonly />
                </div>

                <div class="field">
                    <label>{{ $t ? $t("gender") : "Gender" }}</label>
                    <select v-model="form.gender">
                        <option value="" disabled selected>
                            {{ $t ? $t("gender") : "Gender" }}
                        </option>
                        <option value="male">
                            {{ $t ? $t("male") : "Male" }}
                        </option>
                        <option value="female">
                            {{ $t ? $t("female") : "Female" }}
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
                            v-for="(item, index) in governorates"
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
                        {{ errors.area[0] }}
                    </div>
                </div>

                <div class="field full-width">
                    <label>{{
                        $t ? $t("full_address") : "Full Address"
                    }}</label>
                    <textarea
                        v-model="form.address"
                        :placeholder="$t ? $t('full_address') : ''"
                    ></textarea>
                    <div v-if="errors.address" class="error-msg">
                        {{ errors.address[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("housing_status") : "Housing Status"
                    }}</label>
                    <select v-model="form.housing_status">
                        <option value="" disabled selected>
                            {{ $t ? $t("housing_status") : "Housing Status" }}
                        </option>
                        <option value="rent">
                            {{ $t ? $t("rental") : "Rent" }}
                        </option>
                        <option value="own">
                            {{ $t ? $t("ownership") : "Own" }}
                        </option>
                        <option value="hosted">
                            {{ $t ? $t("hosted") : "Hosted" }}
                        </option>
                    </select>
                    <div v-if="errors.housing_status" class="error-msg">
                        {{ errors.housing_status[0] }}
                    </div>
                </div>

                <div class="field" v-if="form.housing_status == 'rent'">
                    <label>{{ $t ? $t("rent_value") : "Rent Value" }}</label>
                    <input
                        type="number"
                        v-model.number="form.rent_value"
                        :placeholder="$t ? $t('rent_value') : ''"
                    />
                    <div v-if="errors.rent_value" class="error-msg">
                        {{ errors.rent_value[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("marital_status") : "Marital Status"
                    }}</label>
                    <input
                        v-model="form.marital_status"
                        :placeholder="$t ? $t('marital_status') : ''"
                    />
                    <div v-if="errors.marital_status" class="error-msg">
                        {{ errors.marital_status[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("health_status") : "Health Status"
                    }}</label>
                    <input
                        v-model="form.health_status"
                        :placeholder="$t ? $t('health_status') : ''"
                    />
                    <div v-if="errors.health_status" class="error-msg">
                        {{ errors.health_status[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t ? $t("entry_date") : "Entry Date" }}</label>
                    <input type="date" v-model="form.entry_date" />
                    <div v-if="errors.entry_date" class="error-msg">
                        {{ errors.entry_date[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("entry_method") : "Entry Method"
                    }}</label>
                    <select v-model="form.entry_method">
                        <option value="" disabled selected>
                            {{ $t ? $t("entry_method") : "Entry Method" }}
                        </option>
                        <option value="hala_company">
                            {{ $t ? $t("hala_company") : "Hala Company" }}
                        </option>
                        <option value="external_lists">
                            {{ $t ? $t("external_lists") : "External Lists" }}
                        </option>
                        <option value="foreign_lists">
                            {{ $t ? $t("foreign_lists") : "Foreign Lists" }}
                        </option>
                        <option value="medical_transfers">
                            {{
                                $t
                                    ? $t("medical_transfers")
                                    : "Medical Transfers"
                            }}
                        </option>
                    </select>
                    <div v-if="errors.entry_method" class="error-msg">
                        {{ errors.entry_method[0] }}
                    </div>
                </div>
            </div>

            <hr class="divider" />

            <!-- medical -->
            <h3 class="section-title">
                {{ $t ? $t("medical_data") : "Medical Data" }}
            </h3>
            <div class="grid">
                <div class="field">
                    <label>{{
                        $t ? $t("amputation_date") : "Amputation Date"
                    }}</label>
                    <input type="date" v-model="form.amputation_date" />
                    <div v-if="errors.amputation_date" class="error-msg">
                        {{ errors.amputation_date[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("amputation_place") : "Amputation Place"
                    }}</label>
                    <input
                        v-model="form.amputation_place"
                        :placeholder="$t ? $t('amputation_place') : ''"
                    />
                    <div v-if="errors.amputation_place" class="error-msg">
                        {{ errors.amputation_place[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("injury_cause") : "Injury Cause"
                    }}</label>
                    <input
                        v-model="form.injury_cause"
                        :placeholder="$t ? $t('injury_cause') : ''"
                    />
                    <div v-if="errors.injury_cause" class="error-msg">
                        {{ errors.injury_cause[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("amputated_part") : "Amputated Part"
                    }}</label>
                    <input
                        v-model="form.amputated_part"
                        :placeholder="$t ? $t('amputated_part') : ''"
                    />
                    <div v-if="errors.amputated_part" class="error-msg">
                        {{ errors.amputated_part[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("needs_prosthetic") : "Needs Prosthetic?"
                    }}</label>
                    <select v-model="form.needs_prosthetic">
                        <option value="" disabled selected>
                            {{
                                $t
                                    ? $t("needs_prosthetic")
                                    : "Needs Prosthetic?"
                            }}
                        </option>
                        <option :value="true">
                            {{ $t ? $t("yes") : "Yes" }}
                        </option>
                        <option :value="false">
                            {{ $t ? $t("no") : "No" }}
                        </option>
                    </select>
                    <div v-if="errors.needs_prosthetic" class="error-msg">
                        {{ errors.needs_prosthetic[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("received_from_haq") : "Received From Haq?"
                    }}</label>
                    <select v-model="form.received_aid_from_haq">
                        <option value="" disabled selected>
                            {{
                                $t
                                    ? $t("received_from_haq")
                                    : "Received From Haq?"
                            }}
                        </option>
                        <option :value="true">
                            {{ $t ? $t("yes") : "Yes" }}
                        </option>
                        <option :value="false">
                            {{ $t ? $t("no") : "No" }}
                        </option>
                    </select>
                    <div v-if="errors.received_aid_from_haq" class="error-msg">
                        {{ errors.received_aid_from_haq[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t ? $t("aid_type") : "Aid Type" }}</label>
                    <select v-model="form.aid_type">
                        <option value="" disabled selected>
                            {{ $t ? $t("aid_type") : "Aid Type" }}
                        </option>
                        <option value="prosthetic">
                            {{ $t ? $t("prosthetic") : "Prosthetic" }}
                        </option>
                        <option value="wheelchair">
                            {{ $t ? $t("wheelchair") : "Wheelchair" }}
                        </option>
                        <option value="electric_wheelchair">
                            {{
                                $t
                                    ? $t("electric_wheelchair")
                                    : "Electric Wheelchair"
                            }}
                        </option>
                        <option value="walker">
                            {{ $t ? $t("walker") : "Walker" }}
                        </option>
                        <option value="crutch">
                            {{ $t ? $t("crutch") : "Crutch" }}
                        </option>
                    </select>
                    <div v-if="errors.aid_type" class="error-msg">
                        {{ errors.aid_type[0] }}
                    </div>
                </div>

                <div class="field full-width">
                    <label>{{
                        $t ? $t("health_description") : "Health Description"
                    }}</label>
                    <textarea
                        v-model="form.health_description"
                        :placeholder="$t ? $t('health_description') : ''"
                    ></textarea>
                    <div v-if="errors.health_description" class="error-msg">
                        {{ errors.health_description[0] }}
                    </div>
                </div>
            </div>

            <hr class="divider" />

            <!-- companion -->
            <h3 class="section-title">
                {{ $t ? $t("companion_data") : "Companion Data" }}
            </h3>
            <div class="grid">
                <div class="field">
                    <label>{{
                        $t ? $t("has_companion") : "Has Companion?"
                    }}</label>
                    <select v-model="form.has_companion">
                        <option value="" disabled selected>
                            {{ $t ? $t("has_companion") : "Has Companion?" }}
                        </option>
                        <option :value="true">
                            {{ $t ? $t("yes") : "Yes" }}
                        </option>
                        <option :value="false">
                            {{ $t ? $t("no") : "No" }}
                        </option>
                    </select>
                    <div v-if="errors.has_companion" class="error-msg">
                        {{ errors.has_companion[0] }}
                    </div>
                </div>

                <div class="field" v-if="form.has_companion == true">
                    <label>{{
                        $t ? $t("companion_name") : "Companion Name"
                    }}</label>
                    <input
                        v-model="form.companion_name"
                        :placeholder="$t ? $t('companion_name') : ''"
                    />
                    <div v-if="errors.companion_name" class="error-msg">
                        {{ errors.companion_name[0] }}
                    </div>
                </div>

                <div class="field" v-if="form.has_companion == true">
                    <label>{{
                        $t
                            ? $t("companion_national_id")
                            : "Companion National ID"
                    }}</label>
                    <input
                        v-model="form.companion_national_id"
                        :placeholder="$t ? $t('companion_national_id') : ''"
                    />
                    <div v-if="errors.companion_national_id" class="error-msg">
                        {{ errors.companion_national_id[0] }}
                    </div>
                </div>

                <div class="field" v-if="form.has_companion == true">
                    <label>{{
                        $t ? $t("companion_phone") : "Companion Phone"
                    }}</label>
                    <input
                        v-model="form.companion_phone"
                        :placeholder="$t ? $t('companion_phone') : ''"
                    />
                    <div v-if="errors.companion_phone" class="error-msg">
                        {{ errors.companion_phone[0] }}
                    </div>
                </div>
            </div>

            <hr class="divider" />

            <!-- family & economic -->
            <h3 class="section-title">
                {{ $t ? $t("family_economic") : "Family & Economic" }}
            </h3>
            <div class="grid">
                <div class="field">
                    <label>{{
                        $t ? $t("family_members_count") : "Family Members Count"
                    }}</label>
                    <input
                        type="number"
                        v-model.number="form.family_members_count"
                        :placeholder="$t('family_members_count')"
                    />
                    <div v-if="errors.family_members_count" class="error-msg">
                        {{ errors.family_members_count[0] }}
                    </div>
                </div>

                <div class="field full-width">
                    <label>{{
                        $t ? $t("family_composition") : "Family Composition"
                    }}</label>
                    <textarea
                        v-model="form.family_composition"
                        :placeholder="$t('family_composition')"
                    ></textarea>
                    <div v-if="errors.family_composition" class="error-msg">
                        {{ errors.family_composition[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("has_fixed_income") : "Has Fixed Income?"
                    }}</label>
                    <select v-model="form.has_fixed_income">
                        <option value="" disabled selected>
                            {{
                                $t
                                    ? $t("has_fixed_income")
                                    : "Has Fixed Income?"
                            }}
                        </option>
                        <option :value="true">
                            {{ $t ? $t("yes") : "Yes" }}
                        </option>
                        <option :value="false">
                            {{ $t ? $t("no") : "No" }}
                        </option>
                    </select>
                    <div v-if="errors.has_fixed_income" class="error-msg">
                        {{ errors.has_fixed_income[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("monthly_income") : "Monthly Income"
                    }}</label>
                    <input
                        type="number"
                        v-model.number="form.monthly_income"
                        :placeholder="$t('monthly_income')"
                    />
                    <div v-if="errors.monthly_income" class="error-msg">
                        {{ errors.monthly_income[0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{
                        $t ? $t("income_source") : "Income Source"
                    }}</label>
                    <input
                        v-model="form.income_source"
                        :placeholder="$t('income_source')"
                    />
                    <div v-if="errors.income_source" class="error-msg">
                        {{ errors.income_source[0] }}
                    </div>
                </div>

                <div class="field full-width">
                    <label>{{
                        $t ? $t("economic_status") : "Economic Status"
                    }}</label>
                    <textarea
                        v-model="form.economic_status"
                        :placeholder="$t('economic_status')"
                    ></textarea>
                    <div v-if="errors.economic_status" class="error-msg">
                        {{ errors.economic_status[0] }}
                    </div>
                </div>
            </div>

            <hr class="divider" />
            <h3 class="section-title">
                {{ $t ? $t("family_members") : "Family Members" }}
            </h3>
            <div
                v-for="(member, idx) in form.family_members"
                :key="idx"
                class="orphan-card"
            >
                <div class="card-header">
                    <h4 class="orphan-title">
                        {{ $t ? $t("member") : "Member" }} {{ idx + 1 }}
                    </h4>

                    <div class="buttons">
                        <button
                            type="button"
                            class="btn-add"
                            @click="addFamilyMember"
                            v-show="idx == form.family_members.length - 1"
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
                                class="lucide lucide-plus-icon lucide-plus"
                            >
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="btn-delete"
                            @click="removeFamilyMember(idx)"
                            v-show="form.family_members.length > 1"
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
                                class="lucide lucide-trash2-icon lucide-trash-2"
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
                        <label>{{ $t ? $t("name") : "Name" }}</label>
                        <input
                            v-model="member.name"
                            :placeholder="$t('name')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.name`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{ errors[`family_members.${idx}.name`][0] }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("national_id") : "National ID"
                        }}</label>
                        <input
                            v-model="member.national_id"
                            :placeholder="$t('national_id')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.national_id`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{ errors[`family_members.${idx}.national_id`][0] }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("passport_number") : "Passport Number"
                        }}</label>
                        <input
                            v-model="member.passport_number"
                            :placeholder="$t('passport_number')"
                        />
                        <div
                            v-if="
                                errors[`family_members.${idx}.passport_number`]
                            "
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[
                                    `family_members.${idx}.passport_number`
                                ][0]
                            }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("birth_date") : "Birth Date"
                        }}</label>
                        <input
                            type="date"
                            v-model="member.birth_date"
                            @change="updateMemberAge(idx)"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.birth_date`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{ errors[`family_members.${idx}.birth_date`][0] }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{ $t ? $t("age") : "Age" }}</label>
                        <input
                            :value="memberAge(idx)"
                            readonly
                            :placeholder="$t('age')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.age`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{ errors[`family_members.${idx}.age`][0] }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{ $t ? $t("relation") : "Relation" }}</label>
                        <input
                            v-model="member.relation"
                            :placeholder="$t('relation')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.relation`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{ errors[`family_members.${idx}.relation`][0] }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("education_level") : "Education Level"
                        }}</label>
                        <input
                            v-model="member.education_level"
                            :placeholder="$t('education_level')"
                        />
                        <div
                            v-if="
                                errors[`family_members.${idx}.education_level`]
                            "
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[
                                    `family_members.${idx}.education_level`
                                ][0]
                            }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("health_status") : "Health Status"
                        }}</label>
                        <input
                            v-model="member.health_status"
                            :placeholder="$t('health_status')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.health_status`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[`family_members.${idx}.health_status`][0]
                            }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("disease_type") : "Disease Type"
                        }}</label>
                        <input
                            v-model="member.disease_type"
                            :placeholder="$t('disease_type')"
                        />
                        <div
                            v-if="errors[`family_members.${idx}.disease_type`]"
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[`family_members.${idx}.disease_type`][0]
                            }}
                        </div>
                    </div>

                    <div class="field">
                        <label>{{
                            $t ? $t("has_disability") : "Has Disability?"
                        }}</label>
                        <select v-model="member.has_disability">
                            <option value="" disabled selected>
                                {{ $t ? $t("select_option") : "Select" }}
                            </option>
                            <option :value="true">
                                {{ $t ? $t("yes") : "Yes" }}
                            </option>
                            <option :value="false">
                                {{ $t ? $t("no") : "No" }}
                            </option>
                        </select>
                        <div
                            v-if="
                                errors[`family_members.${idx}.has_disability`]
                            "
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[
                                    `family_members.${idx}.has_disability`
                                ][0]
                            }}
                        </div>
                    </div>

                    <div class="field" v-if="member.has_disability">
                        <label>{{
                            $t ? $t("disability_type") : "Disability Type"
                        }}</label>
                        <input
                            v-model="member.disability_type"
                            :placeholder="$t('disability_type')"
                        />
                        <div
                            v-if="
                                errors[`family_members.${idx}.disability_type`]
                            "
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
                        >
                            {{
                                errors[
                                    `family_members.${idx}.disability_type`
                                ][0]
                            }}
                        </div>
                    </div>

                    <div class="field" v-if="member.has_disability">
                        <label>{{
                            $t
                                ? $t("disability_description")
                                : "Disability Description"
                        }}</label>
                        <input
                            v-model="member.disability_description"
                            :placeholder="$t('disability_description')"
                        />
                        <div
                            v-if="
                                errors[
                                    `family_members.${idx}.disability_description`
                                ]
                            "
                            style="
                                width: 100%;
                                margin-top: 0.25rem;
                                font-size: 0.875em;
                                color: #dc3545;
                                text-align: start;
                            "
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
            <h3 class="section-title">{{ $t("Identity confirmation") }}</h3>
            <div class="grid_2">
                <div class="p-4">
                    <h2
                        style="
                            font-size: 1.25rem;
                            font-weight: 700;
                            margin-bottom: 1rem;
                        "
                    >
                        {{ $t("Face photography") }} 📸
                    </h2>

                    <video
                        ref="video"
                        autoplay
                        playsinline
                        width="320"
                        height="240"
                        v-show="!captured"
                        style="
                            border-radius: 1.25rem;
                            border: 1px solid #111111;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1),
                                0 1px 2px rgba(0, 0, 0, 0.06);
                            margin-left: auto;
                            margin-right: auto;
                        "
                    ></video>

                    <canvas
                        ref="canvas"
                        width="320"
                        height="240"
                        style="display: none"
                    ></canvas>

                    <div v-if="preview" class="mt-3">
                        <img
                            :src="preview"
                            alt="preview"
                            style="
                                border-radius: 1.25rem;
                                border-width: 3px;
                                border-style: solid;
                                border-color: #e5e7eb;
                                margin-left: auto;
                                margin-right: auto;
                            "
                        />
                    </div>

                    <div class="mt-4 space-x-2">
                        <button
                            @click="startCamera"
                            style="
                                background-color: #2563eb;
                                color: white;
                                padding-left: 1rem;
                                padding-right: 1rem;
                                padding-top: 0.5rem;
                                padding-bottom: 0.5rem;
                                border-radius: 1rem;
                            "
                            :disabled="streaming"
                            type="button"
                        >
                            {{ $t("Open the camera") }}
                        </button>

                        <button
                            @click="takePhoto"
                            type="button"
                            style="
                                background-color: #eab308;
                                color: white;
                                padding-left: 1rem;
                                padding-right: 1rem;
                                padding-top: 0.5rem;
                                padding-bottom: 0.5rem;
                                border-radius: 1rem;
                            "
                            :disabled="!streaming"
                        >
                            {{ $t("filming") }}
                        </button>

                        <button
                            v-if="captured"
                            @click="uploadPhoto"
                            type="button"
                            style="
                                background-color: #16a34a;
                                color: white;
                                padding-left: 1rem;
                                padding-right: 1rem;
                                padding-top: 0.5rem;
                                padding-bottom: 0.5rem;
                                border-radius: 1rem;
                            "
                        >
                            {{ $t("Upload image") }}
                        </button>

                        <button
                            v-if="captured"
                            @click="retake"
                            type="button"
                            style="
                                background-color: #6b7280;
                                color: white;
                                padding-left: 1rem;
                                padding-right: 1rem;
                                padding-top: 0.5rem;
                                padding-bottom: 0.5rem;
                                border-radius: 1rem;
                            "
                        >
                            {{ $t("restoration") }}
                        </button>
                    </div>

                    <p
                        v-if="message"
                        style="color: #16a34a; margin-top: 0.5rem"
                    >
                        {{ message }}
                    </p>
                    <p
                        v-if="previewError"
                        style="color: #dc2626; margin-top: 0.5rem"
                    >
                        {{ previewError }}
                    </p>
                    <div
                        v-if="errors['image']"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors["image"][0] }}
                    </div>
                </div>

                <div class="p-4">
                    <h2 class="text-xl font-bold mb-2">
                        {{ $t("User Location") }} 📍
                    </h2>

                    <div v-if="error" style="color: #dc3545">
                        {{ error }}
                    </div>

                    <div v-else-if="loading">
                        {{ $t("Getting location...") }}
                    </div>

                    <div v-else-if="center.lat && center.lat">
                        <div class="map-container">
                            <GMapMap
                                :center="center"
                                :zoom="12"
                                map-type-id="roadmap"
                                style="width: 100%; height: 400px"
                            >
                                <GMapMarker
                                    :position="center"
                                    :clickable="true"
                                    :draggable="false"
                                />
                            </GMapMap>
                        </div>
                    </div>

                    <button
                        @click="getLocation"
                        type="button"
                        style="
                            margin-top: 1rem;
                            padding: 0.5rem 1rem;
                            background-color: #2563eb;
                            color: white;
                            border-radius: 1rem;
                        "
                    >
                        {{ $t("Get My Location") }}
                    </button>

                    <div
                        v-if="errors['latitude'] && errors['longitude']"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{
                            errors["latitude"][0] + "-" + errors["longitude"][0]
                        }}
                    </div>
                </div>
            </div>

            <hr class="divider" />
            <h3 class="section-title">{{ $t("Required files") }}</h3>
            <div class="grid_2">
                <div>
                    <label>{{ $t("Parent ID") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange($event, 'case_id')"
                    />
                    <div
                        v-if="errors[`case_id`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`case_id`][0] }}
                    </div>

                    <div v-if="form.case_id" class="file-list">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                background: #f8f9fa;
                                border: 1px solid #dee2e6;
                                border-radius: 1rem;
                                padding: 0.2rem 0.5rem;
                                margin: 0.5rem 0;
                            "
                        >
                            <span>
                                {{ formatBytes(form.case_id.size) }}
                            </span>
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
                                    class="lucide lucide-trash2-icon lucide-trash-2"
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
                        @change="onFileChange($event, 'case_passport')"
                    />
                    <div
                        v-if="errors[`case_passport`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`case_passport`][0] }}
                    </div>
                    <div v-if="form.case_passport" class="file-list">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                background: #f8f9fa;
                                border: 1px solid #dee2e6;
                                border-radius: 1rem;
                                padding: 0.2rem 0.5rem;
                                margin: 0.5rem 0;
                            "
                        >
                            <span>
                                {{ formatBytes(form.case_passport.size) }}
                            </span>

                            <button
                                type="button"
                                class="btn-delete small"
                                @click="removeFile('case_passport')"
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
                                    class="lucide lucide-trash2-icon lucide-trash-2"
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
                    <label>{{ $t("Entry stamp for the case") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange($event, 'case_entry_stamp')"
                    />
                    <div
                        v-if="errors[`case_entry_stamp`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`case_entry_stamp`][0] }}
                    </div>
                    <div v-if="form.case_entry_stamp" class="file-list">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                background: #f8f9fa;
                                border: 1px solid #dee2e6;
                                border-radius: 1rem;
                                padding: 0.2rem 0.5rem;
                                margin: 0.5rem 0;
                            "
                        >
                            <span>
                                {{ formatBytes(form.case_entry_stamp.size) }}
                            </span>

                            <button
                                type="button"
                                class="btn-delete small"
                                @click="removeFile('case_entry_stamp')"
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
                                    class="lucide lucide-trash2-icon lucide-trash-2"
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
                        @change="onFileChange($event, 'medical_reports')"
                    />
                    <div
                        v-if="errors[`medical_reports`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`medical_reports`][0] }}
                    </div>
                    <div v-if="form.medical_reports" class="file-list">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                background: #f8f9fa;
                                border: 1px solid #dee2e6;
                                border-radius: 1rem;
                                padding: 0.2rem 0.5rem;
                                margin: 0.5rem 0;
                            "
                        >
                            <span>
                                {{ formatBytes(form.medical_reports.size) }}
                            </span>

                            <button
                                type="button"
                                class="btn-delete small"
                                @click="removeFile('medical_reports')"
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
                                    class="lucide lucide-trash2-icon lucide-trash-2"
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
                        @change="onFileChange($event, 'rent_contract')"
                    />
                    <div
                        v-if="errors[`rent_contract`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`rent_contract`][0] }}
                    </div>
                    <div v-if="form.rent_contract" class="file-list">
                        <div
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                background: #f8f9fa;
                                border: 1px solid #dee2e6;
                                border-radius: 1rem;
                                padding: 0.2rem 0.5rem;
                                margin: 0.5rem 0;
                            "
                        >
                            <span>
                                {{ formatBytes(form.rent_contract.size) }}
                            </span>

                            <button
                                type="button"
                                class="btn-delete small"
                                @click="removeFile('rent_contract')"
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
                                    class="lucide lucide-trash2-icon lucide-trash-2"
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
                    {{ $t ? $t("save_data") : "Save Data" }}
                </button>
            </div>
        </form>
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
            streaming: false,
            videoStream: null,
            preview: null,
            captured: false,
            message: null,
            previewError: null,

            center: {
                lat: null,
                lng: null,
            },
            error: null,
            loading: false,

            form: {
                full_name: "",
                national_id: "",
                primary_phone: "",
                secondary_phone: "",
                passport_number: "",
                birth_date: "",
                age: null,
                gender: "",
                governorate_id: "",
                city_id: "",
                area_id: "",
                address: "",
                location: "",
                housing_status: "",
                rent_value: "",
                marital_status: "",
                health_status: "",
                entry_date: "",
                entry_method: "",

                amputation_date: "",
                amputation_place: "",
                injury_cause: "",
                amputated_part: "",
                needs_prosthetic: "",
                received_aid_from_haq: "",
                aid_type: "",
                health_description: "",

                has_companion: "",
                companion_name: "",
                companion_national_id: "",
                companion_phone: "",

                family_members_count: "",
                family_composition: "",
                has_fixed_income: "",
                monthly_income: "",
                income_source: "",
                economic_status: "",
                notes: "",

                case_id: null,
                case_passport: null,
                case_entry_stamp: null,
                medical_reports: null,
                rent_contract: null,

                latitude: "",
                longitude: "",
                image: "",

                family_members: [
                    {
                        name: "",
                        national_id: "",
                        passport_number: "",
                        birth_date: "",
                        age: null,
                        relation: "",
                        education_level: "",
                        health_status: "",
                        disease_type: "",
                        has_disability: "",
                        disability_type: "",
                        disability_description: "",
                    },
                ],
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
    },

    methods: {
        onFileChange(event, key, index = null) {
            const file = event.target.files[0];
            if (!file) return;

            const maxSize = 700 * 1024;
            if (file.size > maxSize) {
                this.$swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "⚠️ حجم الملف لا يجب أن يتجاوز 700 كيلوبايت",
                });

                event.target.value = "";
                return;
            }

            if (index === null) {
                this.form[key] = file;
            }
        },

        removeFile(key, index = null) {
            if (index === null) {
                this.form[key] = null;
            } else {
                this.form.orphans[index][key] = null;
            }
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

        async startCamera() {
            this.previewError = null;
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user" },
                });
                this.videoStream = stream;
                this.$refs.video.srcObject = stream;
                this.streaming = true;
            } catch (err) {
                this.previewError = "Error: " + err.message;
            }
        },

        takePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext("2d");

            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(
                (blob) => {
                    if (!blob) return;

                    const file = new File([blob], "captured_image.jpg", {
                        type: "image/jpeg",
                    });
                    this.preview = URL.createObjectURL(file);
                    this.form.image = file;
                    this.captured = true;
                    this.stopCamera();
                },
                "image/jpeg",
                0.9
            );
        },

        retake() {
            this.preview = null;
            this.form.image = null;
            this.captured = false;
            this.message = null;
            this.startCamera();
        },

        stopCamera() {
            if (this.videoStream) {
                this.videoStream.getTracks().forEach((t) => t.stop());
                this.videoStream = null;
                this.streaming = false;
            }
        },

        getLocation() {
            if (!navigator.geolocation) {
                this.error = "Geolocation is not supported by your browser.";
                return;
            }

            this.loading = true;
            this.error = null;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.center.lat = position.coords.latitude;
                    this.center.lng = position.coords.longitude;

                    this.form.latitude = position.coords.latitude;
                    this.form.longitude = position.coords.longitude;
                    this.loading = false;
                },
                (err) => {
                    this.error =
                        "Unable to retrieve your location: " + err.message;
                    this.loading = false;
                }
            );
        },

        async getGovernorates() {
            this.isLoading = true;
            try {
                const { data } = await axios.get("/governorates");
                this.governorates = data;
            } catch (e) {
                // silent
            } finally {
                this.isLoading = false;
            }
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

        addFamilyMember() {
            this.form.family_members.push({
                name: "",
                national_id: "",
                passport_number: "",
                birth_date: "",
                age: null,
                relation: "",
                education_level: "",
                health_status: "",
                disease_type: "",
                has_disability: "",
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
            this.form.family_members[idx].age = this.memberAge(idx);
        },

        appendToFormData(formData, data, parentKey = "") {
            for (let key in data) {
                if (!data.hasOwnProperty(key)) continue;

                let value = data[key];
                let fullKey = parentKey ? `${parentKey}[${key}]` : key;

                // لو Boolean → نحوله 1/0
                if (value === true || value === "true") {
                    formData.append(fullKey, 1);
                } else if (value === false || value === "false") {
                    formData.append(fullKey, 0);
                }

                // لو File → يترفع مباشرة
                else if (value instanceof File) {
                    formData.append(fullKey, value);
                }

                // لو Array → نلف عليه
                else if (Array.isArray(value)) {
                    value.forEach((item, i) => {
                        this.appendToFormData(
                            formData,
                            item,
                            `${fullKey}[${i}]`
                        );
                    });
                }

                // لو Object → Recursive
                else if (typeof value === "object" && value !== null) {
                    this.appendToFormData(formData, value, fullKey);
                }

                // أي قيمة تانية نرفعها
                else {
                    formData.append(fullKey, value ?? "");
                }
            }
        },

        async submit() {
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
            this.errors = {};

            try {
                let fd = new FormData();
                this.appendToFormData(fd, this.form);

                await axios.post(
                    "/users/humanitarian-cases/solidarity-housing/store",
                    fd,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );

                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Success"),
                    text: this.$t("Saved successfully"),
                    icon: "success",
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });
                this.resetForm();
            } catch (error) {
                // handle validation & other errors
                if (error.response) {
                    switch (error.response.status) {
                        case 422:
                            this.errors = error.response.data.errors || {};
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
                        case 401:
                            this.$swal.fire({
                                title: this.$t("Unauthorized"),
                                text: this.$t("You are not logged in"),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;
                        case 403:
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
                        case 404:
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
                        case 500:
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
                        default:
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
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Network Error"),
                        text: this.$t(
                            "No response from the server, please check your connection"
                        ),
                        icon: "error",
                        customClass: { htmlContainer: "swal-text-center" },
                    });
                } else {
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Error"),
                        text: error.message,
                        icon: "error",
                        customClass: { htmlContainer: "swal-text-center" },
                    });
                }
            }
        },

        resetForm() {
            this.form = {
                full_name: "",
                national_id: "",
                primary_phone: "",
                secondary_phone: "",
                passport_number: "",
                birth_date: "",
                age: null,
                gender: "",
                governorate: "",
                area: "",
                address: "",
                location: "",
                housing_status: "",
                rent_value: "",
                marital_status: "",
                health_status: "",
                entry_date: "",
                entry_method: "",

                amputation_date: "",
                amputation_place: "",
                injury_cause: "",
                amputated_part: "",
                needs_prosthetic: "",
                received_aid_from_haq: "",
                aid_type: "",
                health_description: "",

                has_companion: "",
                companion_name: "",
                companion_national_id: "",
                companion_phone: "",

                family_members_count: "",
                family_composition: "",
                has_fixed_income: "",
                monthly_income: "",
                income_source: "",
                economic_status: "",

                notes: "",

                latitude: "",
                longitude: "",
                image: "",

                case_id: null,
                case_passport: null,
                case_entry_stamp: null,
                medical_reports: null,
                rent_contract: null,

                family_members: [
                    {
                        name: "",
                        national_id: "",
                        passport_number: "",
                        birth_date: "",
                        age: null,
                        relation: "",
                        education_level: "",
                        health_status: "",
                        disease_type: "",
                        has_disability: "",
                        disability_type: "",
                        disability_description: "",
                    },
                ],
            };
            this.cities = [];
            this.errors = {};
            this.areas = [];
        },
    },

    beforeUnmount() {
        this.stopCamera();
    },
};
</script>

<style>
@keyframes spin__ {
    100% {
        transform: rotate(360deg);
    }
}
</style>

<style scoped>
/* form wrapper */
.form-wrapper {
    padding: 15px;
    margin: 10px auto;
    background: #ffffff;
    box-sizing: border-box;
}

/* titles */
.title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 12px;
    text-align: center;
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

/* grid */
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

.field {
    display: block;
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
textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    margin-top: 6px;
    box-sizing: border-box;
    font-size: 0.95rem;
}
select {
    width: 100%;
    padding: 6px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    margin-top: 6px;
    box-sizing: border-box;
    font-size: 0.95rem;
}

/* family member card */
.orphan-card {
    border: 1px solid #e6e9ee;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    background: #fcfcfd;
}
.card-header {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.card-header .buttons {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* buttons */
.btn-delete {
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    padding: 8px 10px;
}
.btn-add {
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    padding: 8px 10px;
    margin-bottom: 8px;
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

/* file list */
.file-list {
    list-style: none;
}
.file-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 8px;
    border: 1px solid #e6e9ee;
    border-radius: 6px;
    margin-top: 6px;
    background: #fafafa;
    font-size: 0.95rem;
}
.btn-delete.small {
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    padding: 4px 8px;
}

/* errors */
.error-msg {
    color: #dc3545;
    margin-top: 6px;
    font-size: 0.875rem;
    text-align: start;
}

button {
    margin: 4px;
}

.footer {
    margin: 1rem 0;
}
</style>
