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
                                    $t("Family without breadwinner")
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
                                        name: 'admin.humanitarian.cases.families-without-breadwinner.index',
                                    }"
                                >
                                    {{ $t("Family without breadwinner") }}
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
                        {{ $t("Edit family case") }}
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
                            <!-- بيانات الزوجة (الأم) -->
                            <h3 class="section-title">
                                {{ $t("Wife data (Mother)") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("wife_full_name") }}</label>
                                    <input
                                        v-model="form.wife_full_name"
                                        :placeholder="$t('wife_full_name')"
                                    />
                                    <div
                                        v-if="errors.wife_full_name"
                                        class="error"
                                    >
                                        {{ errors.wife_full_name[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("wife_national_id") }}</label>
                                    <input
                                        v-model="form.wife_national_id"
                                        :placeholder="$t('wife_national_id')"
                                    />
                                    <div
                                        v-if="errors.wife_national_id"
                                        class="error"
                                    >
                                        {{ errors.wife_national_id[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("wife_main_phone") }}</label>
                                    <input
                                        v-model="form.wife_main_phone"
                                        :placeholder="$t('wife_main_phone')"
                                    />
                                    <div
                                        v-if="errors.wife_main_phone"
                                        class="error"
                                    >
                                        {{ errors.wife_main_phone[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{
                                        $t("wife_additional_phone")
                                    }}</label>
                                    <input
                                        v-model="form.wife_additional_phone"
                                        :placeholder="
                                            $t('wife_additional_phone')
                                        "
                                    />
                                    <div
                                        v-if="errors.wife_additional_phone"
                                        class="error"
                                    >
                                        {{ errors.wife_additional_phone[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{
                                        $t("wife_passport_number")
                                    }}</label>
                                    <input
                                        v-model="form.wife_passport_number"
                                        :placeholder="
                                            $t('wife_passport_number')
                                        "
                                    />
                                    <div
                                        v-if="errors.wife_passport_number"
                                        class="error"
                                    >
                                        {{ errors.wife_passport_number[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("wife_birth_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.wife_birth_date"
                                        @change="updateWifeAge"
                                    />
                                    <div
                                        v-if="errors.wife_birth_date"
                                        class="error"
                                    >
                                        {{ errors.wife_birth_date[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("wife_age") }}</label>
                                    <input
                                        :value="computedWifeAge"
                                        placeholder="Auto calculated"
                                        readonly
                                    />
                                    <div v-if="errors.wife_age" class="error">
                                        {{ errors.wife_age[0] }}
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
                                    <label>{{ $t("location") }}</label>
                                    <input
                                        v-model="form.location"
                                        :placeholder="$t('location')"
                                    />
                                    <div v-if="errors.location" class="error">
                                        {{ errors.location[0] }}
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
                                    <select v-model="form.social_status">
                                        <option value="" disabled>
                                            {{ $t("social_status") }}
                                        </option>
                                        <option value="married_husband_abroad">
                                            {{ $t("married_husband_abroad") }}
                                        </option>
                                        <option value="divorced">
                                            {{ $t("divorced") }}
                                        </option>
                                        <option value="widowed_non_martyr">
                                            {{ $t("widowed_non_martyr") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.social_status"
                                        class="error"
                                    >
                                        {{ errors.social_status[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{
                                        $t("wife_health_status")
                                    }}</label>
                                    <input
                                        v-model="form.wife_health_status"
                                        :placeholder="$t('wife_health_status')"
                                    />
                                    <div
                                        v-if="errors.wife_health_status"
                                        class="error"
                                    >
                                        {{ errors.wife_health_status[0] }}
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

                            <!-- بيانات الزوج -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Husband data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("husband_name") }}</label>
                                    <input
                                        v-model="form.husband_name"
                                        :placeholder="$t('husband_name')"
                                    />
                                    <div
                                        v-if="errors.husband_name"
                                        class="error"
                                    >
                                        {{ errors.husband_name[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{
                                        $t("husband_national_id")
                                    }}</label>
                                    <input
                                        v-model="form.husband_national_id"
                                        :placeholder="$t('husband_national_id')"
                                    />
                                    <div
                                        v-if="errors.husband_national_id"
                                        class="error"
                                    >
                                        {{ errors.husband_national_id[0] }}
                                    </div>
                                </div>
                                <div class="field full-width">
                                    <label>{{ $t("husband_location") }}</label>
                                    <input
                                        v-model="form.husband_location"
                                        :placeholder="$t('husband_location')"
                                    />
                                    <div
                                        v-if="errors.husband_location"
                                        class="error"
                                    >
                                        {{ errors.husband_location[0] }}
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
                                v-for="(member, idx) in form.members"
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
                                            @click="addMember"
                                            v-show="
                                                idx === form.members.length - 1
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
                                            @click="removeMember(idx)"
                                            v-show="form.members.length > 1"
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
                                    <!-- باقي الحقول نفس صفحة الإنشاء -->
                                    <div class="field">
                                        <label>{{ $t("name") }}</label>
                                        <input
                                            v-model="member.name"
                                            :placeholder="$t('name')"
                                        />
                                        <div
                                            v-if="errors[`members.${idx}.name`]"
                                            class="error"
                                        >
                                            {{
                                                errors[`members.${idx}.name`][0]
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
                                                    `members.${idx}.national_id`
                                                ]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `members.${idx}.national_id`
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
                                                errors[`members.${idx}.phone`]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `members.${idx}.phone`
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
                                                    `members.${idx}.passport_number`
                                                ]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `members.${idx}.passport_number`
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
                                                    `members.${idx}.birth_date`
                                                ]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `members.${idx}.birth_date`
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
                                                    `members.${idx}.relation`
                                                ]
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors[
                                                    `members.${idx}.relation`
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

                                <!-- مرفقات العضو (مع عرض الملفات الموجودة مسبقًا) -->
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
                                            class="existing-file"
                                        >
                                            <a
                                                :href="member.existing_passport"
                                                target="_blank"
                                                >{{ $t("current_file") }}</a
                                            >
                                        </div>
                                        <div
                                            v-if="member.passport_attachment"
                                            class="file-list"
                                        >
                                            <div class="file-preview">
                                                <span>{{
                                                    member.passport_attachment
                                                        .name
                                                }}</span>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeMemberFile(
                                                            'passport_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    X
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
                                            class="existing-file"
                                        >
                                            <a
                                                :href="
                                                    member.existing_entry_stamp
                                                "
                                                target="_blank"
                                                >{{ $t("current_file") }}</a
                                            >
                                        </div>
                                        <div
                                            v-if="member.entry_stamp_attachment"
                                            class="file-list"
                                        >
                                            <div class="file-preview">
                                                <span>{{
                                                    member
                                                        .entry_stamp_attachment
                                                        .name
                                                }}</span>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeMemberFile(
                                                            'entry_stamp_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    X
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
                                            class="existing-file"
                                        >
                                            <a
                                                :href="
                                                    member.existing_medical_reports
                                                "
                                                target="_blank"
                                                >{{ $t("current_file") }}</a
                                            >
                                        </div>
                                        <div
                                            v-if="
                                                member.medical_reports_attachment
                                            "
                                            class="file-list"
                                        >
                                            <div class="file-preview">
                                                <span>{{
                                                    member
                                                        .medical_reports_attachment
                                                        .name
                                                }}</span>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeMemberFile(
                                                            'medical_reports_attachment',
                                                            idx,
                                                        )
                                                    "
                                                >
                                                    X
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- مرفقات الأم (مع عرض الملفات الحالية) -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Required files for mother") }}
                            </h3>
                            <div class="grid_2">
                                <!-- كل مرفق مع عرض الرابط الحالي إذا موجود -->
                                <div>
                                    <label>{{ $t("mother_id") }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'wife_id_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.existing_wife_id"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="form.existing_wife_id"
                                            target="_blank"
                                            >{{ $t("current_file") }}</a
                                        >
                                    </div>
                                    <div
                                        v-if="form.wife_id_attachment"
                                        class="file-list"
                                    >
                                        <div class="file-preview">
                                            <span>{{
                                                form.wife_id_attachment.name
                                            }}</span>
                                            <button
                                                type="button"
                                                @click="
                                                    removeFile(
                                                        'wife_id_attachment',
                                                    )
                                                "
                                            >
                                                X
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- مرفقات الأم -->
                                <hr class="divider" />
                                <h3 class="section-title">
                                    {{ $t("Required files for mother") }}
                                </h3>
                                <div class="grid_2">
                                    <!-- هوية الأم -->
                                    <div>
                                        <label>{{ $t("mother_id") }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'wife_id_attachment',
                                                )
                                            "
                                        />

                                        <!-- عرض الملف الحالي إذا موجود -->
                                        <div
                                            v-if="form.existing_wife_id"
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="form.existing_wife_id"
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <!-- عرض الملف الجديد إذا تم رفعه -->
                                        <div
                                            v-if="form.wife_id_attachment"
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form.wife_id_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .wife_id_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'wife_id_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="errors.wife_id_attachment"
                                            class="error"
                                        >
                                            {{ errors.wife_id_attachment[0] }}
                                        </div>
                                    </div>

                                    <!-- جواز سفر الأم -->
                                    <div>
                                        <label>{{
                                            $t("mother_passport")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'wife_passport_attachment',
                                                )
                                            "
                                        />

                                        <div
                                            v-if="form.existing_wife_passport"
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    form.existing_wife_passport
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="form.wife_passport_attachment"
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form
                                                            .wife_passport_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .wife_passport_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'wife_passport_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                errors.wife_passport_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .wife_passport_attachment[0]
                                            }}
                                        </div>
                                    </div>

                                    <!-- ختم دخول الأم -->
                                    <div>
                                        <label>{{
                                            $t("mother_entry_stamp")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'wife_entry_stamp_attachment',
                                                )
                                            "
                                        />

                                        <div
                                            v-if="
                                                form.existing_wife_entry_stamp
                                            "
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    form.existing_wife_entry_stamp
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="
                                                form.wife_entry_stamp_attachment
                                            "
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form
                                                            .wife_entry_stamp_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .wife_entry_stamp_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'wife_entry_stamp_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                errors.wife_entry_stamp_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .wife_entry_stamp_attachment[0]
                                            }}
                                        </div>
                                    </div>

                                    <!-- التقرير الطبي للأم -->
                                    <div>
                                        <label>{{
                                            $t("mother_medical_report")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'wife_medical_report_attachment',
                                                )
                                            "
                                        />

                                        <div
                                            v-if="
                                                form.existing_wife_medical_report
                                            "
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    form.existing_wife_medical_report
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="
                                                form.wife_medical_report_attachment
                                            "
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form
                                                            .wife_medical_report_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .wife_medical_report_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'wife_medical_report_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                errors.wife_medical_report_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .wife_medical_report_attachment[0]
                                            }}
                                        </div>
                                    </div>

                                    <!-- عقد الإيجار -->
                                    <div>
                                        <label>{{ $t("rent_contract") }}</label>
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
                                                :href="
                                                    form.existing_rent_contract
                                                "
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

                                        <div
                                            v-if="
                                                errors.rent_contract_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .rent_contract_attachment[0]
                                            }}
                                        </div>
                                    </div>

                                    <!-- شهادة الطلاق -->
                                    <div>
                                        <label>{{
                                            $t("divorce_certificate")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'divorce_certificate_attachment',
                                                )
                                            "
                                        />

                                        <div
                                            v-if="
                                                form.existing_divorce_certificate
                                            "
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    form.existing_divorce_certificate
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="
                                                form.divorce_certificate_attachment
                                            "
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form
                                                            .divorce_certificate_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .divorce_certificate_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'divorce_certificate_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                errors.divorce_certificate_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .divorce_certificate_attachment[0]
                                            }}
                                        </div>
                                    </div>

                                    <!-- شهادة الوفاة -->
                                    <div>
                                        <label>{{
                                            $t("death_certificate")
                                        }}</label>
                                        <input
                                            type="file"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'death_certificate_attachment',
                                                )
                                            "
                                        />

                                        <div
                                            v-if="
                                                form.existing_death_certificate
                                            "
                                            class="existing-file mt-2"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >{{ $t("Current file") }}:</span
                                            >
                                            <a
                                                :href="
                                                    form.existing_death_certificate
                                                "
                                                target="_blank"
                                                class="ml-2 text-blue-600 underline"
                                            >
                                                {{ $t("View file") }}
                                            </a>
                                        </div>

                                        <div
                                            v-if="
                                                form.death_certificate_attachment
                                            "
                                            class="file-list mt-2"
                                        >
                                            <div class="file-preview">
                                                <span
                                                    >{{
                                                        form
                                                            .death_certificate_attachment
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            form
                                                                .death_certificate_attachment
                                                                .size,
                                                        )
                                                    }}</span
                                                >
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'death_certificate_attachment',
                                                        )
                                                    "
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                errors.death_certificate_attachment
                                            "
                                            class="error"
                                        >
                                            {{
                                                errors
                                                    .death_certificate_attachment[0]
                                            }}
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
                wife_full_name: "",
                wife_national_id: "",
                wife_main_phone: "",
                wife_additional_phone: "",
                wife_passport_number: "",
                wife_birth_date: "",
                wife_age: null,
                governorate: "",
                detailed_address: "",
                location: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                wife_health_status: "",
                entry_date_to_egypt: "",
                entry_method: "",
                husband_name: "",
                husband_national_id: "",
                husband_location: "",
                has_fixed_income: false,
                income_value: null,
                income_source: "",
                notes: "",

                // Existing files (لعرض الروابط الحالية)
                existing_wife_id: null,
                existing_wife_passport: null,
                existing_wife_entry_stamp: null,
                existing_wife_medical_report: null,
                existing_rent_contract: null,
                existing_divorce_certificate: null,
                existing_death_certificate: null,

                // New files (للرفع الجديد)
                wife_id_attachment: null,
                wife_passport_attachment: null,
                wife_entry_stamp_attachment: null,
                wife_medical_report_attachment: null,
                rent_contract_attachment: null,
                divorce_certificate_attachment: null,
                death_certificate_attachment: null,

                members: [], // سيتم ملؤها من الـ API
            },
        };
    },
    async created() {
        await this.fetchCase();
    },

    computed: {
        computedWifeAge() {
            if (!this.form.wife_birth_date) return "";
            const bd = new Date(this.form.wife_birth_date);
            const diff = Date.now() - bd.getTime();
            this.form.wife_age = Math.floor(
                diff / (1000 * 60 * 60 * 24 * 365.25),
            );
            return this.form.wife_age;
        },
    },

    methods: {
        async fetchCase() {
            this.isLoading = true;
            try {
                const { data } = await axios.get(
                    `/admin/humanitarian-cases/family-breadwinners/${this.$route.params.id}`,
                );
                this.form = {
                    ...data.family,

                    // حفظ الروابط الحالية للمرفقات الرئيسية (لعرضها)
                    existing_wife_id: data.family.wife_id_attachment,
                    existing_wife_passport:
                        data.family.wife_passport_attachment,
                    existing_wife_entry_stamp:
                        data.family.wife_entry_stamp_attachment,
                    existing_wife_medical_report:
                        data.family.wife_medical_report_attachment,
                    existing_rent_contract:
                        data.family.rent_contract_attachment,
                    existing_divorce_certificate:
                        data.family.divorce_certificate_attachment,
                    existing_death_certificate:
                        data.family.death_certificate_attachment,

                    // تهيئة حقول الرفع الجديدة بـ null (حتى يتم اختيار ملف جديد)
                    wife_id_attachment: null,
                    wife_passport_attachment: null,
                    wife_entry_stamp_attachment: null,
                    wife_medical_report_attachment: null,
                    rent_contract_attachment: null,
                    divorce_certificate_attachment: null,
                    death_certificate_attachment: null,

                    // تحويل أفراد الأسرة مع الحفاظ على المرفقات الحالية
                    members: data.family.members.map((m) => ({
                        id: m.id, // مهم جدًا للتحديث
                        ...m,

                        // حفظ الروابط الحالية لمرفقات العضو
                        existing_passport: m.passport_attachment,
                        existing_entry_stamp: m.entry_stamp_attachment,
                        existing_medical_reports: m.medical_reports_attachment,

                        // تهيئة حقول الرفع الجديدة
                        passport_attachment: null,
                        entry_stamp_attachment: null,
                        medical_reports_attachment: null,
                    })),
                };
            } catch (error) {
                this.$swal.fire({
                    icon: "error",
                    title: "خطأ في تحميل البيانات",
                });
            } finally {
                this.isLoading = false;
            }
        },

        onFileChange(event, key, index = null) {
            const file = event.target.files[0];
            if (!file) return;

            const maxSize = 700 * 1024; // 700 KB
            if (file.size > maxSize) {
                this.$swal.fire({
                    icon: "error",
                    title: this.$t("File too large"),
                    text: this.$t("File size must not exceed 700 KB"),
                });
                event.target.value = "";
                return;
            }

            if (index === null) {
                // مرفقات الأم
                this.form[key] = file;
            } else {
                // مرفقات العضو
                this.form.members[index][key] = file;
            }
        },

        // حذف ملف جديد تم رفعه (للأم)
        removeFile(key) {
            this.form[key] = null;
        },

        // حذف ملف جديد لعضو
        removeMemberFile(key, index) {
            this.form.members[index][key] = null;
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

        // إضافة عضو جديد
        addMember() {
            this.form.members.push({
                name: "",
                national_id: "",
                phone: "",
                passport_number: "",
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

        // حذف عضو (فقط من الـ form، لأنه سيتم حذفه في الباك إند إذا لم يرسل id)
        removeMember(index) {
            this.form.members.splice(index, 1);
        },

        // حساب عمر العضو
        memberAge(index) {
            const birthDate = this.form.members[index].birth_date;
            if (!birthDate) return "";
            const bd = new Date(birthDate);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },

        updateMemberAge(index) {
            this.form.members[index].age = this.memberAge(index);
        },

        // تنسيق حجم الملف
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

        // إرسال النموذج للتحديث
        async submit() {
            this.$swal.fire({
                title: this.$t("Updating..."),
                allowOutsideClick: false,
                didOpen: () => this.$swal.showLoading(),
            });

            try {
                let fd = new FormData();

                // الحقول النصية والأساسية
                const mainFields = [
                    "wife_full_name",
                    "wife_national_id",
                    "wife_main_phone",
                    "wife_additional_phone",
                    "wife_passport_number",
                    "wife_birth_date",
                    "wife_age",
                    "governorate",
                    "detailed_address",
                    "location",
                    "housing_status",
                    "rent_value",
                    "social_status",
                    "wife_health_status",
                    "entry_date_to_egypt",
                    "entry_method",
                    "husband_name",
                    "husband_national_id",
                    "husband_location",
                    "has_fixed_income",
                    "income_value",
                    "income_source",
                    "notes",
                ];

                mainFields.forEach((field) => {
                    fd.append(field, this.form[field] || "");
                });

                // المرفقات الرئيسية (إذا تم رفع ملف جديد فقط)
                const mainAttachments = [
                    "wife_id_attachment",
                    "wife_passport_attachment",
                    "wife_entry_stamp_attachment",
                    "wife_medical_report_attachment",
                    "rent_contract_attachment",
                    "divorce_certificate_attachment",
                    "death_certificate_attachment",
                ];

                mainAttachments.forEach((key) => {
                    if (this.form[key]) {
                        fd.append(key, this.form[key]);
                    }
                });

                // أفراد الأسرة
                this.form.members.forEach((member, i) => {
                    // الحقول النصية للعضو
                    fd.append(`members[${i}][id]`, member.id || ""); // إذا موجود id → تحديث، غير كده → إنشاء
                    fd.append(`members[${i}][name]`, member.name || "");
                    fd.append(
                        `members[${i}][national_id]`,
                        member.national_id || "",
                    );
                    fd.append(`members[${i}][phone]`, member.phone || "");
                    fd.append(
                        `members[${i}][passport_number]`,
                        member.passport_number || "",
                    );
                    fd.append(
                        `members[${i}][birth_date]`,
                        member.birth_date || "",
                    );
                    fd.append(`members[${i}][age]`, member.age || "");
                    fd.append(`members[${i}][relation]`, member.relation || "");
                    fd.append(
                        `members[${i}][education_level]`,
                        member.education_level || "",
                    );
                    fd.append(
                        `members[${i}][health_status]`,
                        member.health_status || "",
                    );
                    fd.append(
                        `members[${i}][disease_type]`,
                        member.disease_type || "",
                    );
                    fd.append(
                        `members[${i}][has_disability]`,
                        member.has_disability ? 1 : 0,
                    );
                    fd.append(
                        `members[${i}][disability_type]`,
                        member.disability_type || "",
                    );
                    fd.append(
                        `members[${i}][disability_description]`,
                        member.disability_description || "",
                    );

                    // مرفقات العضو (إذا تم رفع ملف جديد)
                    if (member.passport_attachment)
                        fd.append(
                            `members[${i}][passport_attachment]`,
                            member.passport_attachment,
                        );
                    if (member.entry_stamp_attachment)
                        fd.append(
                            `members[${i}][entry_stamp_attachment]`,
                            member.entry_stamp_attachment,
                        );
                    if (member.medical_reports_attachment)
                        fd.append(
                            `members[${i}][medical_reports_attachment]`,
                            member.medical_reports_attachment,
                        );
                });

                fd.append("_method", "PATCH");

                // إرسال الطلب
                await axios.post(
                    `/admin/humanitarian-cases/family-breadwinners/update/${this.$route.params.id}`,
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

                // إعادة تحميل البيانات أو الرجوع
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

<style>
@keyframes spin__ {
    100% {
        transform: rotate(360deg);
    }
}
</style>

<style scoped>
.map-container {
    width: 100%;
    height: 400px;
}
/* form wrapper */
.form-wrapper {
    padding: 15px;
    margin: 10px auto;
    background: #ffffff;
    box-sizing: border-box;
}

/* titles */
.title {
    font-size: 1.5rem; /* text-2xl */
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
}

.section-title {
    font-size: 1.125rem; /* text-xl */
    font-weight: 800;
    margin-bottom: 12px;
}

.divider {
    margin: 24px 0;
    border: none;
    height: 1px;
    background: #e5e7eb; /* light gray */
}

/* grid */
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

/* make two columns on wider screens */
@media (min-width: 768px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .grid_2 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* field block */
.field {
    display: block;
}

/* full width field (take both columns) */
.full-width {
    grid-column: 1 / -1;
}

/* labels and inputs */
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
    padding: 4px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    margin-top: 6px;
    box-sizing: border-box;
    font-size: 0.95rem;
}

/* orphan card */
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

/* orphan title */
.orphan-title {
    font-weight: 700;
    font-size: 1rem;
}

/* buttons */
.btn-delete {
    background: #ef4444; /* red-500 */
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    padding: 8px 10px;
}

.btn-add {
    background: #16a34a; /* blue-500 */
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    padding: 8px 10px;
}

/* footer and save button */
.footer {
    margin-top: 20px;
    text-align: center;
}

.btn-save {
    background: #16a34a; /* green-600 */
    color: #fff;
    padding: 10px 18px;
    border: none;
    border-radius: 1rem;
    cursor: pointer;
    font-weight: 600;
}

button {
    margin: 4px;
}
</style>
