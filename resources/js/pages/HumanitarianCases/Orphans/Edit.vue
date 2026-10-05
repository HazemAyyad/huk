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
                                        name: 'admin.humanitarian.cases.orphans.index',
                                    }"
                                >
                                    {{ $t("orphans") }}
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
                            <div class="grid">
                                <div class="field">
                                    <label>{{
                                        $t("guardian_full_name")
                                    }}</label>
                                    <input
                                        v-model="form.full_name"
                                        :placeholder="$t('guardian_full_name')"
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
                                    <label>{{ $t("whatsapp_number") }}</label>
                                    <input
                                        v-model="form.phone"
                                        :placeholder="$t('whatsapp_number')"
                                    />
                                    <div v-if="errors.phone" class="error-msg">
                                        {{ errors.phone[0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("additional_phone") }}</label>
                                    <input
                                        v-model="form.phone2"
                                        :placeholder="$t('additional_phone')"
                                    />
                                    <div v-if="errors.phone2" class="error-msg">
                                        {{ errors.phone2[0] }}
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
                                    <label>{{ $t("relation") }}</label>
                                    <input
                                        v-model="form.relation"
                                        :placeholder="$t('relation')"
                                    />
                                    <div
                                        v-if="errors.relation"
                                        class="error-msg"
                                    >
                                        {{ errors.relation[0] }}
                                    </div>
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
                                            {{ $t("rent") }}
                                        </option>
                                        <option value="own">
                                            {{ $t("own") }}
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
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("deceased_data") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{ $t("orphan_status") }}</label>
                                    <select v-model="form.deceased.type">
                                        <option value="" selected disabled>
                                            {{ $t("orphan_status") }}
                                        </option>
                                        <option value="father_orphan">
                                            {{ $t("father_orphan") }}
                                        </option>
                                        <option value="both_orphan">
                                            {{ $t("both_orphan") }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="errors['deceased.type']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.type"][0] }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="
                                        form.deceased.type == 'father_orphan' ||
                                        form.deceased.type == 'both_orphan'
                                    "
                                >
                                    <label>{{ $t("father_name") }}</label>
                                    <input
                                        v-model="form.deceased.father_name"
                                        :placeholder="$t('father_name')"
                                    />
                                    <div
                                        v-if="errors['deceased.father_name']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.father_name"][0] }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="form.deceased.type == 'both_orphan'"
                                >
                                    <label>{{ $t("mother_name") }}</label>
                                    <input
                                        v-model="form.deceased.mother_name"
                                        :placeholder="$t('mother_name')"
                                    />
                                    <div
                                        v-if="errors['deceased.mother_name']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.mother_name"][0] }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="
                                        form.deceased.type == 'father_orphan' ||
                                        form.deceased.type == 'both_orphan'
                                    "
                                >
                                    <label>{{ $t("father_death_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="
                                            form.deceased.father_death_date
                                        "
                                    />
                                    <div
                                        v-if="
                                            errors['deceased.father_death_date']
                                        "
                                        class="error-msg"
                                    >
                                        {{
                                            errors[
                                                "deceased.father_death_date"
                                            ][0]
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="form.deceased.type == 'both_orphan'"
                                >
                                    <label>{{ $t("mother_death_date") }}</label>
                                    <input
                                        type="date"
                                        v-model="
                                            form.deceased.mother_death_date
                                        "
                                    />
                                    <div
                                        v-if="
                                            errors['deceased.mother_death_date']
                                        "
                                        class="error-msg"
                                    >
                                        {{
                                            errors[
                                                "deceased.mother_death_date"
                                            ][0]
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="
                                        form.deceased.type == 'father_orphan' ||
                                        form.deceased.type == 'both_orphan'
                                    "
                                >
                                    <label>{{
                                        $t("father_death_reason")
                                    }}</label>
                                    <input
                                        v-model="
                                            form.deceased.father_death_reason
                                        "
                                        :placeholder="$t('father_death_reason')"
                                    />
                                    <div
                                        v-if="
                                            errors[
                                                'deceased.father_death_reason'
                                            ]
                                        "
                                        class="error-msg"
                                    >
                                        {{
                                            errors[
                                                "deceased.father_death_reason"
                                            ][0]
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="form.deceased.type == 'both_orphan'"
                                >
                                    <label>{{
                                        $t("mother_death_reason")
                                    }}</label>
                                    <input
                                        v-model="
                                            form.deceased.mother_death_reason
                                        "
                                        :placeholder="$t('mother_death_reason')"
                                    />
                                    <div
                                        v-if="
                                            errors[
                                                'deceased.mother_death_reason'
                                            ]
                                        "
                                        class="error-msg"
                                    >
                                        {{
                                            errors[
                                                "deceased.mother_death_reason"
                                            ][0]
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="
                                        form.deceased.type == 'father_orphan' ||
                                        form.deceased.type == 'both_orphan'
                                    "
                                >
                                    <label>{{ $t("father_job") }}</label>
                                    <input
                                        v-model="form.deceased.father_job"
                                        :placeholder="$t('father_job')"
                                    />
                                    <div
                                        v-if="errors['deceased.father_job']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.father_job"][0] }}
                                    </div>
                                </div>
                                <div
                                    class="field"
                                    v-if="form.deceased.type == 'both_orphan'"
                                >
                                    <label>{{ $t("mother_job") }}</label>
                                    <input
                                        v-model="form.deceased.mother_job"
                                        :placeholder="$t('mother_job')"
                                    />
                                    <div
                                        v-if="errors['deceased.mother_job']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.mother_job"][0] }}
                                    </div>
                                </div>
                                <div class="field">
                                    <label>{{ $t("monthly_income") }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.deceased.income"
                                        :placeholder="$t('monthly_income')"
                                    />
                                    <div
                                        v-if="errors['deceased.income']"
                                        class="error-msg"
                                    >
                                        {{ errors["deceased.income"][0] }}
                                    </div>
                                </div>
                            </div>

                            <hr class="divider" />

                            <h3 class="section-title">
                                {{ $t("orphans_data") }}
                            </h3>
                            <div
                                v-for="(o, idx) in form.orphans"
                                :key="idx"
                                class="orphan-card"
                            >
                                <div class="card-header">
                                    <div>
                                        <h4 class="orphan-title">
                                            {{ $t("orphan_number") }}
                                            {{ idx + 1 }}
                                        </h4>
                                    </div>
                                    <div class="buttons">
                                        <button
                                            type="button"
                                            class="btn-add"
                                            @click="addOrphan"
                                            v-show="
                                                idx == form.orphans.length - 1
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
                                                class="lucide lucide-plus-icon lucide-plus"
                                            >
                                                <path d="M5 12h14" />
                                                <path d="M12 5v14" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-delete"
                                            @click="removeOrphan(idx)"
                                            v-show="
                                                idx ||
                                                (!idx &&
                                                    form.orphans.length > 1)
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
                                        <label>{{ $t("name") }}</label>
                                        <input
                                            v-model="o.name"
                                            :placeholder="$t('name')"
                                        />
                                        <div
                                            v-if="errors[`orphans.${idx}.name`]"
                                            class="error-msg"
                                        >
                                            {{
                                                errors[`orphans.${idx}.name`][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("birth_date") }}</label>
                                        <input
                                            type="date"
                                            v-model="o.birth_date"
                                            @change="updateOrphanAge(idx)"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `orphans.${idx}.birth_date`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.birth_date`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("age") }}</label>
                                        <input
                                            :value="orphanAge(idx)"
                                            readonly
                                        />
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("gender") }}</label>
                                        <select v-model="o.gender">
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
                                        <div
                                            v-if="
                                                errors[`orphans.${idx}.gender`]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.gender`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("lives_with_guardian")
                                        }}</label>
                                        <select v-model="o.lives_with_guardian">
                                            <option value="" disabled selected>
                                                {{ $t("lives_with_guardian") }}
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
                                                    `orphans.${idx}.lives_with_guardian`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.lives_with_guardian`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("education_stage")
                                        }}</label>
                                        <input
                                            v-model="o.education_stage"
                                            :placeholder="$t('education_stage')"
                                        />
                                        <div
                                            v-if="
                                                errors[
                                                    `orphans.${idx}.education_stage`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.education_stage`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{ $t("study_type") }}</label>
                                        <select v-model="o.study_type">
                                            <option value="" disabled selected>
                                                {{ $t("study_type") }}
                                            </option>
                                            <option value="online">
                                                {{ $t("online") }}
                                            </option>
                                            <option value="onsite">
                                                {{ $t("onsite") }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="
                                                errors[
                                                    `orphans.${idx}.study_type`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.study_type`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("has_chronic_disease")
                                        }}</label>
                                        <select v-model="o.has_chronic_disease">
                                            <option value="" disabled selected>
                                                {{ $t("has_chronic_disease") }}
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
                                                    `orphans.${idx}.has_chronic_disease`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.has_chronic_disease`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="field"
                                        v-if="o.has_chronic_disease"
                                    >
                                        <label>{{
                                            $t("disease_description")
                                        }}</label>
                                        <input
                                            v-model="o.disease_description"
                                            :placeholder="
                                                $t('disease_description')
                                            "
                                        />
                                    </div>
                                    <div class="field">
                                        <label>{{
                                            $t("has_disability")
                                        }}</label>
                                        <select v-model="o.has_disability">
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
                                        <div
                                            v-if="
                                                errors[
                                                    `orphans.${idx}.has_disability`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.has_disability`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                    <div class="field" v-if="o.has_disability">
                                        <label>{{
                                            $t("disability_type")
                                        }}</label>
                                        <select v-model="o.disability_type">
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
                                        <div
                                            v-if="
                                                errors[
                                                    `orphans.${idx}.disability_type`
                                                ]
                                            "
                                            class="error-msg"
                                        >
                                            {{
                                                errors[
                                                    `orphans.${idx}.disability_type`
                                                ][0]
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <hr class="divider" />

                                <h3 class="section-title">
                                    {{ $t("Required files") }}
                                </h3>
                                <div class="grid_2 mt-2">
                                    <div>
                                        <label>{{
                                            $t("Passport for an orphan")
                                        }}</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'orphan_passport',
                                                    idx
                                                )
                                            "
                                        />
                                        <!-- عرض الملف الحالي -->
                                        <div
                                            v-if="
                                                existingOrphanFiles[idx]
                                                    ?.orphan_passport
                                            "
                                            class="existing-file"
                                        >
                                            <a
                                                :href="
                                                    existingOrphanFiles[idx]
                                                        .orphan_passport
                                                "
                                                target="_blank"
                                            >
                                                عرض الملف الحالي
                                            </a>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeExistingOrphanFile(
                                                        idx,
                                                        'orphan_passport'
                                                    )
                                                "
                                            >
                                                حذف
                                            </button>
                                        </div>
                                        <!-- الملف الجديد -->
                                        <div
                                            v-if="o.orphan_passport"
                                            class="file-list"
                                        >
                                            <div class="file-item">
                                                <span>
                                                    {{ o.orphan_passport.name }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            o.orphan_passport
                                                                .size
                                                        )
                                                    }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'orphan_passport',
                                                            idx
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
                                        <label>{{ $t("Entry stamp") }}</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'orphan_entry_stamp',
                                                    idx
                                                )
                                            "
                                        />
                                        <div
                                            v-if="
                                                existingOrphanFiles[idx]
                                                    ?.orphan_entry_stamp
                                            "
                                            class="existing-file"
                                        >
                                            <a
                                                :href="
                                                    existingOrphanFiles[idx]
                                                        .orphan_entry_stamp
                                                "
                                                target="_blank"
                                            >
                                                عرض الملف الحالي
                                            </a>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeExistingOrphanFile(
                                                        idx,
                                                        'orphan_entry_stamp'
                                                    )
                                                "
                                            >
                                                حذف
                                            </button>
                                        </div>
                                        <div
                                            v-if="o.orphan_entry_stamp"
                                            class="file-list"
                                        >
                                            <div class="file-item">
                                                <span>
                                                    {{
                                                        o.orphan_entry_stamp
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            o.orphan_entry_stamp
                                                                .size
                                                        )
                                                    }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'orphan_entry_stamp',
                                                            idx
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
                                        <label>{{
                                            $t("birth certificate")
                                        }}</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            @change="
                                                onFileChange(
                                                    $event,
                                                    'orphan_birth_certificate',
                                                    idx
                                                )
                                            "
                                        />
                                        <div
                                            v-if="
                                                existingOrphanFiles[idx]
                                                    ?.orphan_birth_certificate
                                            "
                                            class="existing-file"
                                        >
                                            <a
                                                :href="
                                                    existingOrphanFiles[idx]
                                                        .orphan_birth_certificate
                                                "
                                                target="_blank"
                                            >
                                                عرض الملف الحالي
                                            </a>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeExistingOrphanFile(
                                                        idx,
                                                        'orphan_birth_certificate'
                                                    )
                                                "
                                            >
                                                حذف
                                            </button>
                                        </div>
                                        <div
                                            v-if="o.orphan_birth_certificate"
                                            class="file-list"
                                        >
                                            <div class="file-item">
                                                <span>
                                                    {{
                                                        o
                                                            .orphan_birth_certificate
                                                            .name
                                                    }}
                                                    —
                                                    {{
                                                        formatBytes(
                                                            o
                                                                .orphan_birth_certificate
                                                                .size
                                                        )
                                                    }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="btn-delete small"
                                                    @click="
                                                        removeFile(
                                                            'orphan_birth_certificate',
                                                            idx
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
                                            onFileChange($event, 'guardian_id')
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.guardian_id"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="existingFiles.guardian_id"
                                            target="_blank"
                                        >
                                            عرض الملف الحالي
                                        </a>
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting('guardian_id')
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.guardian_id"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>
                                                {{ form.guardian_id.name }} —
                                                {{
                                                    formatBytes(
                                                        form.guardian_id.size
                                                    )
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile('guardian_id')
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
                                    <label>{{ $t("Parent's passport") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'guardian_passport'
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.guardian_passport"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="
                                                existingFiles.guardian_passport
                                            "
                                            target="_blank"
                                        >
                                            عرض الملف الحالي
                                        </a>
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'guardian_passport'
                                                )
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.guardian_passport"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>
                                                {{
                                                    form.guardian_passport.name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.guardian_passport
                                                            .size
                                                    )
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'guardian_passport'
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
                                    <label>{{
                                        $t("Entry stamp for the guardian")
                                    }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'guardian_entry_stamp'
                                            )
                                        "
                                    />
                                    <div
                                        v-if="
                                            existingFiles.guardian_entry_stamp
                                        "
                                        class="existing-file"
                                    >
                                        <a
                                            :href="
                                                existingFiles.guardian_entry_stamp
                                            "
                                            target="_blank"
                                        >
                                            عرض الملف الحالي
                                        </a>
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'guardian_entry_stamp'
                                                )
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.guardian_entry_stamp"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>
                                                {{
                                                    form.guardian_entry_stamp
                                                        .name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form
                                                            .guardian_entry_stamp
                                                            .size
                                                    )
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'guardian_entry_stamp'
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
                                                'medical_reports'
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
                                        >
                                            عرض الملف الحالي
                                        </a>
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'medical_reports'
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
                                            <span>
                                                {{ form.medical_reports.name }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.medical_reports
                                                            .size
                                                    )
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'medical_reports'
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
                                                'rent_contract'
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
                                        >
                                            عرض الملف الحالي
                                        </a>
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
                                            <span>
                                                {{ form.rent_contract.name }} —
                                                {{
                                                    formatBytes(
                                                        form.rent_contract.size
                                                    )
                                                }}
                                            </span>
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
                                <div>
                                    <label>{{ $t("death certificate") }}</label>
                                    <input
                                        type="file"
                                        class="form-control"
                                        @change="
                                            onFileChange(
                                                $event,
                                                'death_certificate'
                                            )
                                        "
                                    />
                                    <div
                                        v-if="existingFiles.death_certificate"
                                        class="existing-file"
                                    >
                                        <a
                                            :href="
                                                existingFiles.death_certificate
                                            "
                                            target="_blank"
                                        >
                                            عرض الملف الحالي
                                        </a>
                                        <button
                                            type="button"
                                            class="btn-delete small"
                                            @click="
                                                removeExisting(
                                                    'death_certificate'
                                                )
                                            "
                                        >
                                            حذف
                                        </button>
                                    </div>
                                    <div
                                        v-if="form.death_certificate"
                                        class="file-list"
                                    >
                                        <div class="file-item">
                                            <span>
                                                {{
                                                    form.death_certificate.name
                                                }}
                                                —
                                                {{
                                                    formatBytes(
                                                        form.death_certificate
                                                            .size
                                                    )
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="btn-delete small"
                                                @click="
                                                    removeFile(
                                                        'death_certificate'
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
            existingOrphanFiles: [],
            form: {
                case_code: "",
                full_name: "",
                national_id: "",
                phone: "",
                phone2: "",
                birth_date: "",
                relation: "",
                gender: "",
                governorate_id: "",
                city_id: "",
                area_id: "",
                address: "",
                housing_status: "",
                rent_value: null,
                deceased: {
                    type: "",
                    father_name: "",
                    mother_name: "",
                    father_death_date: "",
                    mother_death_date: "",
                    father_death_reason: "",
                    mother_death_reason: "",
                    father_job: "",
                    mother_job: "",
                    income: null,
                },
                orphans: [],
                guardian_id: null,
                guardian_passport: null,
                guardian_entry_stamp: null,
                medical_reports: null,
                rent_contract: null,
                death_certificate: null,
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
                    `/admin/humanitarian-cases/orphans/${this.$route.params.id}/get-item`
                );
                const c = data;

                this.form = {
                    case_code: c.case_code,
                    full_name: c.full_name,
                    national_id: c.national_id,
                    phone: c.phone,
                    phone2: c.phone2,
                    birth_date: c.birth_date,
                    relation: c.relation,
                    gender: c.gender,
                    governorate_id: c.governorate_id,
                    city_id: c.city_id,
                    area_id: c.area_id,
                    address: c.address,
                    housing_status: c.housing_status,
                    rent_value: c.rent_value || null,
                    deceased: {
                        type: c.deceased?.type || "",
                        father_name: c.deceased?.father_name || "",
                        mother_name: c.deceased?.mother_name || "",
                        father_death_date: c.deceased?.father_death_date || "",
                        mother_death_date: c.deceased?.mother_death_date || "",
                        father_death_reason:
                            c.deceased?.father_death_reason || "",
                        mother_death_reason:
                            c.deceased?.mother_death_reason || "",
                        father_job: c.deceased?.father_job || "",
                        mother_job: c.deceased?.mother_job || "",
                        income: c.deceased?.income || null,
                    },
                    orphans: c.orphans.map((o) => ({
                        name: o.name,
                        birth_date: o.birth_date,
                        gender: o.gender,
                        lives_with_guardian: o.lives_with_guardian == 1,
                        education_stage: o.education_stage,
                        study_type: o.study_type,
                        has_chronic_disease: o.has_chronic_disease == 1,
                        disease_description: o.disease_description,
                        has_disability: o.has_disability == 1,
                        disability_type: o.disability_type,
                        orphan_passport: null,
                        orphan_entry_stamp: null,
                        orphan_birth_certificate: null,
                    })),
                    guardian_id: null,
                    guardian_passport: null,
                    guardian_entry_stamp: null,
                    medical_reports: null,
                    rent_contract: null,
                    death_certificate: null,
                };

                this.existingFiles = data.files || {};
                this.existingOrphanFiles =
                    data.orphan_files || c.orphans.map(() => ({}));

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
                const { data } = await axios.get("/governorates/get");
                this.governorates = data;
            } catch (error) {}
        },
        async getCities(id) {
            if (!id) {
                this.cities = [];
                return;
            }
            try {
                const { data } = await axios.get(`/${id}/cities/get`);
                this.cities = data;
            } catch (error) {}
        },
        async getAreas(id) {
            if (!id) {
                this.areas = [];
                return;
            }
            try {
                const { data } = await axios.get(`/${id}/areas/get`);
                this.areas = data;
            } catch (error) {}
        },
        onFileChange(event, key, index = null) {
            const file = event.target.files[0];
            if (!file) return;
            const maxSize = 700 * 1024;
            if (file.size > maxSize) {
                this.$swal.fire({
                    icon: "error",
                    title: "خطأ في الحجم",
                    text: "حجم الملف لا يجب أن يتجاوز 700 كيلوبايت",
                });
                event.target.value = "";
                return;
            }
            if (index === null) {
                this.form[key] = file;
            } else {
                this.form.orphans[index][key] = file;
            }
        },
        removeFile(key, index = null) {
            if (index === null) {
                this.form[key] = null;
            } else {
                this.form.orphans[index][key] = null;
            }
        },
        removeExisting(key) {
            this.existingFiles[key] = null;
            this.form[`${key}_delete`] = 1;
        },
        removeExistingOrphanFile(orphanIndex, key) {
            if (this.existingOrphanFiles[orphanIndex]) {
                this.existingOrphanFiles[orphanIndex][key] = null;
            }
            this.form.orphans[orphanIndex][`${key}_delete`] = 1;
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
        addOrphan() {
            this.form.orphans.push({
                name: "",
                birth_date: "",
                gender: "",
                lives_with_guardian: "",
                education_stage: "",
                study_type: "",
                has_chronic_disease: "",
                disease_description: "",
                has_disability: "",
                disability_type: "",
                orphan_passport: null,
                orphan_entry_stamp: null,
                orphan_birth_certificate: null,
            });
            this.existingOrphanFiles.push({});
        },
        removeOrphan(i) {
            this.form.orphans.splice(i, 1);
            this.existingOrphanFiles.splice(i, 1);
        },
        orphanAge(i) {
            const b = this.form.orphans[i].birth_date;
            if (!b) return "";
            const bd = new Date(b);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        updateOrphanAge(i) {
            this.form.orphans[i].age = this.orphanAge(i);
        },
        async submit() {
            this.$swal.fire({
                title: this.$t("Loading..."),
                allowOutsideClick: false,
                didOpen: () => this.$swal.showLoading(),
            });

            try {
                const fd = new FormData();

                // الحقول الأساسية
                for (let key in this.form) {
                    if (key !== "orphans" && key !== "deceased") {
                        if (this.form[key] !== null && this.form[key] !== "") {
                            fd.append(key, this.form[key]);
                        }
                    }
                }

                // deceased
                for (let key in this.form.deceased) {
                    fd.append(
                        `deceased[${key}]`,
                        this.form.deceased[key] ?? ""
                    );
                }

                // orphans
                this.form.orphans.forEach((orphan, i) => {
                    for (let key in orphan) {
                        let value = orphan[key];
                        if (
                            [
                                "lives_with_guardian",
                                "has_chronic_disease",
                                "has_disability",
                            ].includes(key)
                        ) {
                            fd.append(`orphans[${i}][${key}]`, value ? 1 : 0);
                        } else if (value instanceof File) {
                            fd.append(`orphans[${i}][${key}]`, value);
                        } else if (orphan[`${key}_delete`]) {
                            fd.append(`orphans[${i}][${key}_delete]`, 1);
                        } else {
                            fd.append(`orphans[${i}][${key}]`, value ?? "");
                        }
                    }
                });

                // حذف ملفات الوصي
                Object.keys(this.existingFiles).forEach((key) => {
                    if (this.existingFiles[key] === null) {
                        fd.append(`${key}_delete`, 1);
                    }
                });

                await axios.post(
                    `/admin/humanitarian-cases/orphans/${this.$route.params.id}/update`,
                    fd,
                    { headers: { "Content-Type": "multipart/form-data" } }
                );

                this.$swal.fire("تم!", "تم تحديث البيانات بنجاح", "success");
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.$swal.fire({
                        title: this.$t("Validation Error"),
                        text: this.$t("Please check the form and try again"),
                        icon: "warning",
                    });
                } else {
                    this.$swal.fire("خطأ", "حدث خطأ أثناء التحديث", "error");
                }
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
    gap: 0.5rem;
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
    font-size: 1rem;
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
