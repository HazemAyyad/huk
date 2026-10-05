<template>
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
        <div class="grid">
            <div class="field">
                <label>{{ $t("guardian_full_name") }}</label>
                <input
                    v-model="form.full_name"
                    :placeholder="$t('guardian_full_name')"
                />
                <div
                    v-if="errors.full_name"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                <div
                    v-if="errors.phone"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.phone[0] }}
                </div>
            </div>

            <div class="field">
                <label>{{ $t("additional_phone") }}</label>
                <input
                    v-model="form.phone2"
                    :placeholder="$t('additional_phone')"
                />
                <div
                    v-if="errors.phone2"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.phone2[0] }}
                </div>
            </div>

            <div class="field">
                <label>{{ $t("birth_date") }}</label>
                <input
                    type="date"
                    v-model="form.birth_date"
                    :placeholder="$t('birth_date')"
                />
                <div
                    v-if="errors.birth_date"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.birth_date[0] }}
                </div>
            </div>

            <div class="field">
                <label>{{ $t("age") }}</label>
                <input :value="computedAge" :placeholder="$t('age')" readonly />
                <div
                    v-if="errors.age"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.age[0] }}
                </div>
            </div>

            <div class="field">
                <label>{{ $t("relation") }}</label>
                <input v-model="form.relation" :placeholder="$t('relation')" />
                <div
                    v-if="errors.relation"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    <option value="male">{{ $t("male") }}</option>
                    <option value="female">{{ $t("female") }}</option>
                </select>
                <div
                    v-if="errors.gender"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
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
                <select v-model="form.city_id" @change="getAreas(form.city_id)">
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
                <label>{{ $t("full_address") }}</label>
                <textarea
                    v-model="form.address"
                    :placeholder="$t('full_address')"
                ></textarea>
                <div
                    v-if="errors.address"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    <option value="rent">{{ $t("rent") }}</option>
                    <option value="own">{{ $t("own") }}</option>
                    <option value="hosted">{{ $t("hosted") }}</option>
                </select>
                <div
                    v-if="errors.housing_status"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.housing_status[0] }}
                </div>
            </div>

            <div class="field" v-if="form.housing_status == 'rent'">
                <label>{{ $t("rent_value") }}</label>
                <input
                    type="number"
                    v-model.number="form.rent_value"
                    :placeholder="$t('rent_value')"
                />
                <div
                    v-if="errors.rent_value"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.rent_value[0] }}
                </div>
            </div>
        </div>

        <hr class="divider" />

        <h3 class="section-title">{{ $t("deceased_data") }}</h3>
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
                    <option value="both_orphan">{{ $t("both_orphan") }}</option>
                </select>
                <div
                    v-if="errors['deceased.type']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.father_name"][0] }}
                </div>
            </div>

            <div class="field" v-if="form.deceased.type == 'both_orphan'">
                <label>{{ $t("mother_name") }}</label>
                <input
                    v-model="form.deceased.mother_name"
                    :placeholder="$t('mother_name')"
                />
                <div
                    v-if="errors['deceased.mother_name']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    v-model="form.deceased.father_death_date"
                    :placeholder="$t('father_death_date')"
                />
                <div
                    v-if="errors['deceased.father_death_date']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.father_death_date"][0] }}
                </div>
            </div>

            <div class="field" v-if="form.deceased.type == 'both_orphan'">
                <label>{{ $t("mother_death_date") }}</label>
                <input
                    type="date"
                    v-model="form.deceased.mother_death_date"
                    :placeholder="$t('mother_death_date')"
                />
                <div
                    v-if="errors['deceased.mother_death_date']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.mother_death_date"][0] }}
                </div>
            </div>

            <div
                class="field"
                v-if="
                    form.deceased.type == 'father_orphan' ||
                    form.deceased.type == 'both_orphan'
                "
            >
                <label>{{ $t("father_death_reason") }}</label>
                <input
                    v-model="form.deceased.father_death_reason"
                    :placeholder="$t('father_death_reason')"
                />
                <div
                    v-if="errors['deceased.father_death_reason']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.father_death_reason"][0] }}
                </div>
            </div>

            <div class="field" v-if="form.deceased.type == 'both_orphan'">
                <label>{{ $t("mother_death_reason") }}</label>
                <input
                    v-model="form.deceased.mother_death_reason"
                    :placeholder="$t('mother_death_reason')"
                />
                <div
                    v-if="errors['deceased.mother_death_reason']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.mother_death_reason"][0] }}
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.father_job"][0] }}
                </div>
            </div>

            <div class="field" v-if="form.deceased.type == 'both_orphan'">
                <label>{{ $t("mother_job") }}</label>
                <input
                    v-model="form.deceased.mother_job"
                    :placeholder="$t('mother_job')"
                />
                <div
                    v-if="errors['deceased.mother_job']"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors["deceased.income"][0] }}
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

                <p v-if="message" style="color: #16a34a; margin-top: 0.5rem">
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

                <div v-else-if="loading">{{ $t("Getting location...") }}</div>

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
                    {{ errors["latitude"][0] + "-" + errors["longitude"][0] }}
                </div>
            </div>
        </div>

        <h3 class="section-title">{{ $t("orphans_data") }}</h3>
        <div v-for="(o, idx) in form.orphans" :key="idx" class="orphan-card">
            <div class="card-header">
                <div>
                    <h4 class="orphan-title">
                        {{ $t("orphan_number") }} {{ idx + 1 }}
                    </h4>
                </div>

                <div class="buttons">
                    <button
                        type="button"
                        class="btn-add"
                        @click="addOrphan"
                        v-show="idx == form.orphans.length - 1"
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
                        v-show="idx || (!idx && form.orphans.length > 1)"
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
                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid">
                <div class="field">
                    <label>{{ $t("name") }}</label>
                    <input v-model="o.name" :placeholder="$t('name')" />

                    <div
                        v-if="errors[`orphans.${idx}.name`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.name`][0] }}
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
                        v-if="errors[`orphans.${idx}.birth_date`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.birth_date`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("age") }}</label>
                    <input
                        :value="orphanAge(idx)"
                        :placeholder="$t('age')"
                        readonly
                    />
                    <div
                        v-if="errors[`orphans.${idx}.age`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.age`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("gender") }}</label>
                    <select v-model="o.gender">
                        <option value="" disabled selected>
                            {{ $t("gender") }}
                        </option>
                        <option value="male">{{ $t("male") }}</option>
                        <option value="female">{{ $t("female") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.gender`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.gender`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("lives_with_guardian") }}</label>
                    <select v-model="o.lives_with_guardian">
                        <option value="" disabled selected>
                            {{ $t("lives_with_guardian") }}
                        </option>
                        <option :value="true">{{ $t("yes") }}</option>
                        <option :value="false">{{ $t("no") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.lives_with_guardian`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.lives_with_guardian`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("education_stage") }}</label>
                    <input
                        v-model="o.education_stage"
                        :placeholder="$t('education_stage')"
                    />
                    <div
                        v-if="errors[`orphans.${idx}.education_stage`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.education_stage`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("study_type") }}</label>
                    <select v-model="o.study_type">
                        <option value="" disabled selected>
                            {{ $t("study_type") }}
                        </option>
                        <option value="online">{{ $t("online") }}</option>
                        <option value="onsite">{{ $t("onsite") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.study_type`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.study_type`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("has_chronic_disease") }}</label>
                    <select v-model="o.has_chronic_disease">
                        <option disabled selected value="">
                            {{ $t("has_chronic_disease") }}
                        </option>
                        <option :value="true">{{ $t("yes") }}</option>
                        <option :value="false">{{ $t("no") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.has_chronic_disease`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.has_chronic_disease`][0] }}
                    </div>
                </div>

                <div class="field" v-if="o.has_chronic_disease">
                    <label>{{ $t("disease_description") }}</label>
                    <input
                        v-model="o.disease_description"
                        :placeholder="$t('disease_description')"
                    />
                    <div
                        v-if="errors[`orphans.${idx}.has_chronic_disease`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.has_chronic_disease`][0] }}
                    </div>
                </div>

                <div class="field">
                    <label>{{ $t("has_disability") }}</label>
                    <select v-model="o.has_disability">
                        <option value="" disabled selected>
                            {{ $t("has_disability") }}
                        </option>
                        <option :value="true">{{ $t("yes") }}</option>
                        <option :value="false">{{ $t("no") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.has_disability`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.has_disability`][0] }}
                    </div>
                </div>

                <div class="field" v-if="o.has_disability">
                    <label>{{ $t("disability_type") }}</label>
                    <select v-model="o.disability_type">
                        <option value="" disabled selected>
                            {{ $t("disability_type") }}
                        </option>
                        <option value="physical">{{ $t("physical") }}</option>
                        <option value="mental">{{ $t("mental") }}</option>
                        <option value="both">{{ $t("both") }}</option>
                    </select>
                    <div
                        v-if="errors[`orphans.${idx}.disability_type`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.disability_type`][0] }}
                    </div>
                </div>
            </div>

            <hr class="divider" />
            <h3 class="section-title">{{ $t("Required files") }}</h3>
            <div class="grid_2 mt-2">
                <div>
                    <label>{{ $t("Passport for an orphan") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange($event, 'orphan_passport', idx)"
                    />
                    <div
                        v-if="o.orphan_passport"
                        class="file-list"
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
                            {{ o.orphan_passport.name }} —
                            {{ formatBytes(o.orphan_passport.size) }}
                        </span>

                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('orphan_passport', idx)"
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
                    <div
                        v-if="errors[`orphans.${idx}.orphan_passport`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.orphan_passport`][0] }}
                    </div>
                </div>

                <div>
                    <label>{{ $t("Entry stamp") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="
                            onFileChange($event, 'orphan_entry_stamp', idx)
                        "
                    />
                    <div
                        v-if="o.orphan_entry_stamp"
                        class="file-list"
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
                        {{ o.orphan_entry_stamp.name }} —
                        {{ formatBytes(o.orphan_entry_stamp.size) }}
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('orphan_entry_stamp', idx)"
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
                    <div
                        v-if="errors[`orphans.${idx}.orphan_entry_stamp`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`orphans.${idx}.orphan_entry_stamp`][0] }}
                    </div>
                </div>

                <div>
                    <label>{{ $t("birth certificate") }}</label>
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
                        v-if="o.orphan_birth_certificate"
                        class="file-list"
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
                        {{ o.orphan_birth_certificate.name }} —
                        {{ formatBytes(o.orphan_birth_certificate.size) }}
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('orphan_birth_certificate', idx)"
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

                    <div
                        v-if="errors[`orphans.${idx}.orphan_birth_certificate`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{
                            errors[`orphans.${idx}.orphan_birth_certificate`][0]
                        }}
                    </div>
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
                    @change="onFileChange($event, 'guardian_id')"
                />
                <div v-if="form.guardian_id" class="file-list">
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
                            {{ form.guardian_id.name }} —
                            {{ formatBytes(form.guardian_id.size) }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('guardian_id')"
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

                <div
                    v-if="errors.guardian_id"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.guardian_id[0] }}
                </div>
            </div>

            <div>
                <label>{{ $t("Parent's passport") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'guardian_passport')"
                />
                <div v-if="form.guardian_passport" class="file-list">
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
                            {{ form.guardian_passport.name }} —
                            {{ formatBytes(form.guardian_passport.size) }}
                        </span>

                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('guardian_passport')"
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
                <div
                    v-if="errors.guardian_passport"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.guardian_passport[0] }}
                </div>
            </div>

            <div>
                <label>{{ $t("Entry stamp for the guardian") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'guardian_entry_stamp')"
                />
                <div v-if="form.guardian_entry_stamp" class="file-list">
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
                            {{ form.guardian_entry_stamp.name }} —
                            {{ formatBytes(form.guardian_entry_stamp.size) }}
                        </span>

                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('guardian_entry_stamp')"
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
                <div
                    v-if="errors.guardian_entry_stamp"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.guardian_entry_stamp[0] }}
                </div>
            </div>

            <div>
                <label>{{ $t("Medical reports") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'medical_reports')"
                />
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
                            {{ form.medical_reports.name }} —
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
                <div
                    v-if="errors.medical_reports"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.medical_reports[0] }}
                </div>
            </div>

            <div>
                <label>{{ $t("lease") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'rent_contract')"
                />
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
                            {{ form.rent_contract.name }} —
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

                <div
                    v-if="errors.rent_contract"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.rent_contract[0] }}
                </div>
            </div>

            <div>
                <label>{{ $t("death certificate") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'death_certificate')"
                />
                <div v-if="form.death_certificate" class="file-list">
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
                            {{ form.death_certificate.name }} —
                            {{ formatBytes(form.death_certificate.size) }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('death_certificate')"
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
                <div
                    v-if="errors.death_certificate"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.death_certificate[0] }}
                </div>
            </div>
        </div>

        <div class="footer">
            <button type="submit" class="btn-save">
                {{ $t("save_data") }}
            </button>
        </div>
    </form>
</template>

<script>
export default {
    data() {
        return {
            isLoading: true,

            governorates: [],
            errors: [],
            cities: [],
            areas: [],

            streaming: false,
            captured: false,
            loading: false,

            videoStream: null,
            previewError: null,
            preview: null,
            message: null,
            error: null,

            center: {
                lat: null,
                lng: null,
            },

            form: {
                full_name: "",
                national_id: "",
                phone: "",
                phone2: "",
                age: "",
                birth_date: "",
                relation: "",
                gender: "",
                governorate_id: "",
                city_id: "",
                area_id: "",
                address: "",
                housing_status: "",
                rent_value: "",
                marital_status: "",
                family_count: "",
                entry_date: "",
                entry_method: "",
                latitude: "",
                longitude: "",
                image: "",

                guardian_id: null,
                guardian_passport: null,
                guardian_entry_stamp: null,
                medical_reports: null,
                rent_contract: null,
                death_certificate: null,

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
                    income: "",
                },

                orphans: [
                    {
                        name: "",
                        birth_date: "",
                        age: null,
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
            this.form.age = Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
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

            // تخزين الملف كما هو بدون Base64
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

        addOrphan() {
            this.form.orphans.push({
                name: "",
                birth_date: "",
                age: null,
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
        },

        removeOrphan(i) {
            this.form.orphans.splice(i, 1);
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

        async getGovernorates() {
            this.isLoading = true;

            try {
                const { data } = await axios.get("/governorates/get");
                this.governorates = data;
                this.isLoading = false;
            } catch (error) {
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

            try {
                let fd = new FormData();

                // إضافة الحقول العادية
                for (let key in this.form) {
                    if (key !== "orphans" && key !== "deceased") {
                        fd.append(key, this.form[key]);
                    }
                }

                // إضافة الـ deceased
                for (let key in this.form.deceased) {
                    fd.append(`deceased[${key}]`, this.form.deceased[key]);
                }

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
                            value = value ? 1 : 0;
                        }

                        if (
                            [
                                "orphan_passport",
                                "orphan_entry_stamp",
                                "orphan_birth_certificate",
                            ].includes(key)
                        ) {
                            if (value) {
                                fd.append(`orphans[${i}][${key}]`, value);
                            } else {
                                fd.append(`orphans[${i}][${key}]`, null); // حط null إذا الملف مش موجود
                            }
                        } else {
                            fd.append(`orphans[${i}][${key}]`, value);
                        }
                    }
                });

                // إرسال البيانات للباك اند
                const res = await axios.post(
                    "/users/humanitarian-cases/orphans/store",
                    fd,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );

                this.streaming = false;
                this.captured = false;
                this.loading = false;

                this.videoStream = null;
                this.previewError = null;
                this.preview = null;
                this.message = null;
                this.error = null;

                this.center = {
                    lat: null,
                    lng: null,
                };

                this.form = {
                    full_name: "",
                    national_id: "",
                    phone: "",
                    phone2: "",
                    age: "",
                    birth_date: "",
                    relation: "",
                    gender: "",
                    governorate_id: "",
                    city_id: "",
                    area_id: "",
                    address: "",
                    housing_status: "",
                    rent_value: "",
                    marital_status: "",
                    family_count: "",
                    entry_date: "",
                    entry_method: "",
                    latitude: "",
                    longitude: "",
                    image: "",

                    guardian_id: null,
                    guardian_passport: null,
                    guardian_entry_stamp: null,
                    medical_reports: null,
                    rent_contract: null,
                    death_certificate: null,

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
                        income: "",
                    },

                    orphans: [
                        {
                            name: "",
                            birth_date: "",
                            age: null,
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
                        },
                    ],
                };

                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Success"),
                    text: this.$t("Success"),
                    icon: "success",
                    customClass: {
                        htmlContainer: "swal-text-center",
                    },
                });
            } catch (error) {
                if (error.response) {
                    switch (error.response.status) {
                        case 422:
                            this.errors = error.response.data.errors;

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

                        case 401: // unauthorized
                            this.$swal.fire({
                                title: this.$t("Unauthorized"),
                                text: this.$t("You are not logged in"),
                                icon: "error",
                                customClass: {
                                    htmlContainer: "swal-text-center",
                                },
                            });
                            break;

                        case 403: // forbidden
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

                        case 500: // server error
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

                        default: // أي Error تاني
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
                    // مفيش response من السيرفر
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Network Error"),
                        text: this.$t(
                            "No response from the server, please check your connection"
                        ),
                        icon: "error",
                        customClass: {
                            htmlContainer: "swal-text-center",
                        },
                    });
                } else {
                    // خطأ في إعداد الطلب
                    this.$swal.fire({
                        allowOutsideClick: false,
                        title: this.$t("Error"),
                        text: error.message,
                        icon: "error",
                        customClass: {
                            htmlContainer: "swal-text-center",
                        },
                    });
                }
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
