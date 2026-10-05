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
                                    $t("Stranded families")
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
                                        name: 'admin.humanitarian.cases.stranded-families.index',
                                    }"
                                >
                                    {{ $t("Stranded families") }}
                                </router-link>
                            </li>
                            <li class="breadcrumb-item active">
                                {{ $t("Create") }}
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
                        {{ $t("Create stranded family case") }}
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
                            <!-- بيانات رب الأسرة (الأب) -->
                            <h3 class="section-title">
                                {{ $t("Head of family data (Father)") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("head_full_name") }}</label>
                                    <input
                                        v-model="form.head_full_name"
                                        :placeholder="$t('head_full_name')"
                                    />
                                    <div
                                        v-if="errors.head_full_name"
                                        class="error"
                                    >
                                        {{ errors.head_full_name[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("head_national_id") }}</label>
                                    <input
                                        v-model="form.head_national_id"
                                        :placeholder="$t('head_national_id')"
                                    />
                                    <div
                                        v-if="errors.head_national_id"
                                        class="error"
                                    >
                                        {{ errors.head_national_id[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("head_main_phone") }}</label>
                                    <input
                                        v-model="form.head_main_phone"
                                        :placeholder="$t('head_main_phone')"
                                    />
                                    <div
                                        v-if="errors.head_main_phone"
                                        class="error"
                                    >
                                        {{ errors.head_main_phone[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("head_additional_phone")
                                    }}</label>
                                    <input
                                        v-model="form.head_additional_phone"
                                        :placeholder="
                                            $t('head_additional_phone')
                                        "
                                    />
                                    <div
                                        v-if="errors.head_additional_phone"
                                        class="error"
                                    >
                                        {{ errors.head_additional_phone[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("head_passport_number")
                                    }}</label>
                                    <input
                                        v-model="form.head_passport_number"
                                        :placeholder="
                                            $t('head_passport_number')
                                        "
                                    />
                                    <div
                                        v-if="errors.head_passport_number"
                                        class="error"
                                    >
                                        {{ errors.head_passport_number[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("head_birth_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="form.head_birth_date"
                                        @change="updateHeadAge"
                                    />
                                    <div
                                        v-if="errors.head_birth_date"
                                        class="error"
                                    >
                                        {{ errors.head_birth_date[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("head_age") }}</label>
                                    <input
                                        :value="computedHeadAge"
                                        placeholder="Auto calculated"
                                        readonly
                                    />
                                    <div v-if="errors.head_age" class="error">
                                        {{ errors.head_age[0] }}
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
                                        <option value="" disabled selected>
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
                                        $t("head_health_status")
                                    }}</label>
                                    <input
                                        v-model="form.head_health_status"
                                        :placeholder="$t('head_health_status')"
                                    />
                                    <div
                                        v-if="errors.head_health_status"
                                        class="error"
                                    >
                                        {{ errors.head_health_status[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("head_is_employed") }}</label>
                                    <select v-model="form.head_is_employed">
                                        <option value="" disabled selected>
                                            {{ $t("head_is_employed") }}
                                        </option>
                                        <option :value="true">
                                            {{ $t("yes") }}
                                        </option>
                                        <option :value="false">
                                            {{ $t("no") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors.head_is_employed"
                                        class="error"
                                    >
                                        {{ errors.head_is_employed[0] }}
                                    </div>
                                </div>

                                <div class="field" v-if="form.head_is_employed">
                                    <label>{{ $t("head_job") }}</label>
                                    <input
                                        v-model="form.head_job"
                                        :placeholder="$t('head_job')"
                                    />
                                    <div v-if="errors.head_job" class="error">
                                        {{ errors.head_job[0] }}
                                    </div>
                                </div>

                                <div class="field" v-if="form.head_is_employed">
                                    <label>{{ $t("head_income") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.head_income"
                                        :placeholder="$t('head_income')"
                                    />
                                    <div
                                        v-if="errors.head_income"
                                        class="error"
                                    >
                                        {{ errors.head_income[0] }}
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
                                        <option value="" disabled selected>
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

                            <!-- بيانات الزوجة -->
                            <hr class="divider" />
                            <h3 class="section-title">{{ $t("Wife data") }}</h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("wife_name") }}</label>
                                    <input
                                        v-model="form.wife_name"
                                        :placeholder="$t('wife_name')"
                                    />
                                    <div v-if="errors.wife_name" class="error">
                                        {{ errors.wife_name[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("wife_phone") }}</label>
                                    <input
                                        v-model="form.wife_phone"
                                        :placeholder="$t('wife_phone')"
                                    />
                                    <div v-if="errors.wife_phone" class="error">
                                        {{ errors.wife_phone[0] }}
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

                                <div class="field full-width">
                                    <label>{{ $t("economic_notes") }}</label>
                                    <textarea
                                        v-model="form.economic_notes"
                                        :placeholder="$t('economic_notes')"
                                    ></textarea>
                                    <div
                                        v-if="errors.economic_notes"
                                        class="error"
                                    >
                                        {{ errors.economic_notes[0] }}
                                    </div>
                                </div>
                            </div>

                            <!-- أفراد الأسرة (الأبناء وغيرهم عدا الزوجة) -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Family members") }}
                            </h3>
                            <div
                                v-for="(member, idx) in form.members"
                                :key="idx"
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
                                            v-show="
                                                idx || form.members.length > 1
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
                                            placeholder="Auto calculated"
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
                                            <option value="" disabled selected>
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
                                            <option value="" disabled selected>
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
                                                        removeMemberFile(
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
                                                        removeMemberFile(
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
                                                        removeMemberFile(
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

                            <!-- مرفقات رب الأسرة والزوجة -->
                            <hr class="divider" />
                            <h3 class="section-title">
                                {{ $t("Required files") }}
                            </h3>
                            <div class="grid_2">
                                <!-- رب الأسرة -->
                                <div>
                                    <label>{{
                                        $t("head_id_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'head_id_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.head_id_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form.head_id_attachment.name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.head_id_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'head_id_attachment',
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
                                        $t("head_passport_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'head_passport_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.head_passport_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .head_passport_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .head_passport_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'head_passport_attachment',
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
                                        $t("head_entry_stamp_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'head_entry_stamp_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="form.head_entry_stamp_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .head_entry_stamp_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .head_entry_stamp_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'head_entry_stamp_attachment',
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
                                        $t("head_medical_reports_attachment")
                                    }}</label>
                                    <input
                                        type="file"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'head_medical_reports_attachment',
                                            )
                                        "
                                    />
                                    <div
                                        v-if="
                                            form.head_medical_reports_attachment
                                        "
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form
                                                        .head_medical_reports_attachment
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .head_medical_reports_attachment
                                                            .size,
                                                    )
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'head_medical_reports_attachment',
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

                                <!-- الزوجة -->
                                <div>
                                    <label>{{
                                        $t("wife_id_attachment")
                                    }}</label>
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
                                        v-if="form.wife_id_attachment"
                                        class="file-list mt-2"
                                    >
                                        <div class="file-preview">
                                            <span
                                                >{{
                                                    form.wife_id_attachment.name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.wife_id_attachment
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
                                </div>

                                <div>
                                    <label>{{
                                        $t("wife_passport_attachment")
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
                                </div>

                                <div>
                                    <label>{{
                                        $t("wife_entry_stamp_attachment")
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
                                        v-if="form.wife_entry_stamp_attachment"
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
                                </div>
                            </div>

                            <div class="footer">
                                <button type="submit" class="btn-save">
                                    {{ $t("save_data") }}
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
            isLoading: false,
            errors: {},
            governorates: [],
            cities: [],
            areas: [],

            form: {
                head_full_name: "",
                head_national_id: "",
                head_main_phone: "",
                head_additional_phone: "",
                head_passport_number: "",
                head_birth_date: "",
                head_age: null,
                governorate: "",
                detailed_address: "",
                location: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                head_health_status: "",
                head_is_employed: false,
                head_job: "",
                head_income: null,
                entry_date_to_egypt: "",
                entry_method: "",
                wife_name: "",
                wife_phone: "",
                wife_national_id: "",
                economic_notes: "",
                head_id_attachment: null,
                head_passport_attachment: null,
                head_entry_stamp_attachment: null,
                head_medical_reports_attachment: null,
                rent_contract_attachment: null,
                wife_id_attachment: null,
                wife_passport_attachment: null,
                wife_entry_stamp_attachment: null,
                members: [
                    {
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
                    },
                ],
            },
        };
    },
    computed: {
        computedHeadAge() {
            if (!this.form.head_birth_date) return "";
            const bd = new Date(this.form.head_birth_date);
            const diff = Date.now() - bd.getTime();
            this.form.head_age = Math.floor(
                diff / (1000 * 60 * 60 * 24 * 365.25),
            );
            return this.form.head_age;
        },
    },
    methods: {
        updateHeadAge() {
            this.computedHeadAge;
        },
        memberAge(idx) {
            const b = this.form.members[idx].birth_date;
            if (!b) return "";
            const bd = new Date(b);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        updateMemberAge(idx) {
            this.form.members[idx].age = this.memberAge(idx);
        },
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
            });
        },
        removeMember(idx) {
            this.form.members.splice(idx, 1);
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
                this.form.members[index][key] = file;
            }
        },
        removeFile(key) {
            this.form[key] = null;
        },
        removeMemberFile(key, index) {
            this.form.members[index][key] = null;
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

        async submit() {
            this.$swal.fire({
                title: this.$t("Loading..."),
                allowOutsideClick: false,
                didOpen: () => this.$swal.showLoading(),
            });

            try {
                let fd = new FormData();

                // الحقول الأساسية
                const mainFields = Object.keys(this.form).filter(
                    (k) => !k.includes("attachment") && k !== "members",
                );
                mainFields.forEach((field) => {
                    fd.append(field, this.form[field] || "");
                });

                // المرفقات الرئيسية
                const mainAttachments = [
                    "head_id_attachment",
                    "head_passport_attachment",
                    "head_entry_stamp_attachment",
                    "head_medical_reports_attachment",
                    "rent_contract_attachment",
                    "wife_id_attachment",
                    "wife_passport_attachment",
                    "wife_entry_stamp_attachment",
                ];
                mainAttachments.forEach((key) => {
                    if (this.form[key]) fd.append(key, this.form[key]);
                });

                // أفراد الأسرة
                this.form.members.forEach((member, i) => {
                    Object.keys(member).forEach((mkey) => {
                        let value = member[mkey];
                        if (value instanceof File) {
                            fd.append(`members[${i}][${mkey}]`, value);
                        } else if (typeof value === "boolean") {
                            fd.append(`members[${i}][${mkey}]`, value ? 1 : 0);
                        } else {
                            fd.append(`members[${i}][${mkey}]`, value || "");
                        }
                    });
                });

                const res = await axios.post(
                    `/${document
                        .getElementsByTagName("html")[0]
                        .getAttribute(
                            "lang",
                        )}/admin/humanitarian-cases/stranded-families/store`,
                    fd,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    },
                );

                this.resetForm();
                this.$swal.close();

                const phone =
                    "2" + res.data.case.head_main_phone.replace(/\D/g, "");
                const message = `تم إضافة حالتكم بنجاح في حالات العالقين\nكود الحالة: ${res.data.case.id}\nشكراً لتعاونكم`;
                const url = `https://wa.me/${phone}?text=${encodeURIComponent(
                    message,
                )}`;
                window.open(url, "_blank");
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                }
                this.$swal.fire({
                    icon: "error",
                    title: this.$t("Error"),
                    text:
                        error.response?.data?.message ||
                        this.$t("Something went wrong"),
                });
            }
        },
        resetForm() {
            this.errors = {};
            this.form = {
                head_full_name: "",
                head_national_id: "",
                head_main_phone: "",
                head_additional_phone: "",
                head_passport_number: "",
                head_birth_date: "",
                head_age: null,
                governorate: "",
                detailed_address: "",
                location: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                head_health_status: "",
                head_is_employed: false,
                head_job: "",
                head_income: null,
                entry_date_to_egypt: "",
                entry_method: "",
                wife_name: "",
                wife_phone: "",
                wife_national_id: "",
                economic_notes: "",
                head_id_attachment: null,
                head_passport_attachment: null,
                head_entry_stamp_attachment: null,
                head_medical_reports_attachment: null,
                rent_contract_attachment: null,
                wife_id_attachment: null,
                wife_passport_attachment: null,
                wife_entry_stamp_attachment: null,
                members: [
                    {
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
                    },
                ],
            };
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
