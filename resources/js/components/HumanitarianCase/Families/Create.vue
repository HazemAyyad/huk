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
                <label>{{ $t("wife_full_name") }}</label>
                <input
                    v-model="form.wife_full_name"
                    :placeholder="$t('wife_full_name')"
                />
                <div
                    v-if="errors.wife_full_name"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.wife_main_phone[0] }}
                </div>
            </div>
            <div class="field">
                <label>{{ $t("wife_additional_phone") }}</label>
                <input
                    v-model="form.wife_additional_phone"
                    :placeholder="$t('wife_additional_phone')"
                />
                <div
                    v-if="errors.wife_additional_phone"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.wife_additional_phone[0] }}
                </div>
            </div>
            <div class="field">
                <label>{{ $t("wife_birth_date") }}</label>
                <input
                    type="date"
                    v-model="form.wife_birth_date"
                    :placeholder="$t('wife_birth_date')"
                />
                <div
                    v-if="errors.wife_birth_date"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.wife_birth_date[0] }}
                </div>
            </div>
            <div class="field">
                <label>{{ $t("wife_age") }}</label>
                <input
                    :value="computedWifeAge"
                    :placeholder="$t('wife_age')"
                    readonly
                />
                <div
                    v-if="errors.wife_age"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.wife_age[0] }}
                </div>
            </div>
            <div class="field">
                <label>{{ $t("social_status") }}</label>
                <select v-model="form.social_status">
                    <option value="" disabled selected>
                        {{ $t("social_status") }}
                    </option>
                    <option value="widow">{{ $t("widow") }}</option>
                    <option value="divorced">{{ $t("divorced") }}</option>
                    <option value="abandoned">{{ $t("abandoned") }}</option>
                </select>
                <div
                    v-if="errors.social_status"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.social_status[0] }}
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
                <label>{{ $t("detailed_address") }}</label>
                <textarea
                    v-model="form.detailed_address"
                    :placeholder="$t('detailed_address')"
                ></textarea>
                <div
                    v-if="errors.detailed_address"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
            <div class="field">
                <label>{{ $t("entry_date_to_egypt") }}</label>
                <input type="date" v-model="form.entry_date_to_egypt" />
                <div
                    v-if="errors.entry_date_to_egypt"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
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
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.entry_method[0] }}
                </div>
            </div>
        </div>

        <hr class="divider" />
        <h3 class="section-title">{{ $t("husband_data") }}</h3>
        <div class="grid">
            <div class="field">
                <label>{{ $t("husband_name") }}</label>
                <input
                    v-model="form.husband_name"
                    :placeholder="$t('husband_name')"
                />
                <div
                    v-if="errors.husband_name"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.husband_name[0] }}
                </div>
            </div>
            <div class="field">
                <label>{{ $t("husband_national_id") }}</label>
                <input
                    v-model="form.husband_national_id"
                    :placeholder="$t('husband_national_id')"
                />
                <div
                    v-if="errors.husband_national_id"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.husband_national_id[0] }}
                </div>
            </div>
            <div class="field full-width">
                <label>{{ $t("husband_location") }}</label>
                <textarea
                    v-model="form.husband_location"
                    :placeholder="$t('husband_location')"
                ></textarea>
                <div
                    v-if="errors.husband_location"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.husband_location[0] }}
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
                <div v-else-if="center.lat && center.lng">
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
                    v-if="errors['latitude'] || errors['longitude']"
                    style="width: 100%; margin-top: 0.25rem"
                >
                    {{ errors.latitude ? errors.latitude[0] : "" }}
                    {{ errors.longitude ? errors.longitude[0] : "" }}
                </div>
            </div>
        </div>

        <hr class="divider" />
        <h3 class="section-title">{{ $t("family_members_data") }}</h3>
        <div
            v-for="(member, idx) in form.members"
            :key="idx"
            class="member-card"
        >
            <div class="card-header">
                <div>
                    <h4 class="member-title">
                        {{ $t("member_number") }} {{ idx + 1 }}
                    </h4>
                </div>
                <div class="buttons">
                    <button
                        type="button"
                        class="btn-add"
                        @click="addMember"
                        v-show="idx === form.members.length - 1"
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
                    <input v-model="member.name" :placeholder="$t('name')" />
                    <div
                        v-if="errors[`members.${idx}.name`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.name`][0] }}
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
                        v-if="errors[`members.${idx}.birth_date`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.birth_date`][0] }}
                    </div>
                </div>
                <div class="field">
                    <label>{{ $t("age") }}</label>
                    <input
                        :value="memberAge(idx)"
                        :placeholder="$t('age')"
                        readonly
                    />
                    <div
                        v-if="errors[`members.${idx}.age`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.age`][0] }}
                    </div>
                </div>
                <div class="field">
                    <label>{{ $t("relation") }}</label>
                    <input
                        v-model="member.relation"
                        :placeholder="$t('relation')"
                    />
                    <div
                        v-if="errors[`members.${idx}.relation`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.relation`][0] }}
                    </div>
                </div>
                <div class="field">
                    <label>{{ $t("education_level") }}</label>
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
                    <label>{{ $t("has_disability") }}</label>
                    <select v-model="member.has_disability">
                        <option value="" disabled selected>
                            {{ $t("has_disability") }}
                        </option>
                        <option :value="true">{{ $t("yes") }}</option>
                        <option :value="false">{{ $t("no") }}</option>
                    </select>
                    <div
                        v-if="errors[`members.${idx}.has_disability`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.has_disability`][0] }}
                    </div>
                </div>
                <div class="field" v-if="member.has_disability">
                    <label>{{ $t("disability_type") }}</label>
                    <select v-model="member.disability_type">
                        <option value="" disabled selected>
                            {{ $t("disability_type") }}
                        </option>
                        <option value="physical">{{ $t("physical") }}</option>
                        <option value="mental">{{ $t("mental") }}</option>
                        <option value="both">{{ $t("both") }}</option>
                    </select>
                </div>
                <div class="field" v-if="member.has_disability">
                    <label>{{ $t("disability_description") }}</label>
                    <textarea
                        v-model="member.disability_description"
                    ></textarea>
                </div>
            </div>
            <hr class="divider" />
            <h3 class="section-title">{{ $t("Required files") }}</h3>
            <div class="grid_2 mt-2">
                <div>
                    <label>{{ $t("Passport") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="
                            onFileChange($event, 'passport_attachment', idx)
                        "
                    />
                    <div
                        v-if="member.passport_attachment"
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
                            {{ member.passport_attachment.name }} —
                            {{ formatBytes(member.passport_attachment.size) }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="
                                removeMemberFile('passport_attachment', idx)
                            "
                        >
                            ×
                        </button>
                    </div>
                    <div
                        v-if="errors[`members.${idx}.passport_attachment`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.passport_attachment`][0] }}
                    </div>
                </div>
                <div>
                    <label>{{ $t("Entry stamp") }}</label>
                    <input
                        type="file"
                        class="form-control"
                        @change="
                            onFileChange($event, 'entry_stamp_attachment', idx)
                        "
                    />
                    <div
                        v-if="member.entry_stamp_attachment"
                        class="file-list"
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: Never center;
                            background: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 1rem;
                            padding: 0.2rem 0.5rem;
                            margin: 0.5rem 0;
                        "
                    >
                        <span>
                            {{ member.entry_stamp_attachment.name }} —
                            {{
                                formatBytes(member.entry_stamp_attachment.size)
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="
                                removeMemberFile('entry_stamp_attachment', idx)
                            "
                        >
                            ×
                        </button>
                    </div>
                    <div
                        v-if="errors[`members.${idx}.entry_stamp_attachment`]"
                        style="
                            width: 100%;
                            margin-top: 0.25rem;
                            font-size: 0.875em;
                            color: #dc3545;
                            text-align: start;
                        "
                    >
                        {{ errors[`members.${idx}.entry_stamp_attachment`][0] }}
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
                                'medical_reports_attachment',
                                idx
                            )
                        "
                    />
                    <div
                        v-if="member.medical_reports_attachment"
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
                            {{ member.medical_reports_attachment.name }} —
                            {{
                                formatBytes(
                                    member.medical_reports_attachment.size
                                )
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="
                                removeMemberFile(
                                    'medical_reports_attachment',
                                    idx
                                )
                            "
                        >
                            ×
                        </button>
                    </div>
                    <div
                        v-if="
                            errors[`members.${idx}.medical_reports_attachment`]
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
                                `members.${idx}.medical_reports_attachment`
                            ][0]
                        }}
                    </div>
                </div>
            </div>
        </div>

        <hr class="divider" />
        <h3 class="section-title">{{ $t("Required files") }}</h3>
        <div class="grid_2">
            <div>
                <label>{{ $t("wife_id_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'wife_id_attachment')"
                />
                <div v-if="form.wife_id_attachment" class="file-list">
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
                            {{ form.wife_id_attachment.name }} —
                            {{ formatBytes(form.wife_id_attachment.size) }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('wife_id_attachment')"
                        >
                            ×
                        </button>
                    </div>
                </div>
                <div
                    v-if="errors.wife_id_attachment"
                    style="
                        width: 100%;
                        margin-top: 0.25rem;
                        font-size: 0.875em;
                        color: #dc3545;
                        text-align: start;
                    "
                >
                    {{ errors.wife_id_attachment[0] }}
                </div>
            </div>
            <div>
                <label>{{ $t("wife_passport_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'wife_passport_attachment')"
                />
                <div v-if="form.wife_passport_attachment" class="file-list">
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
                            {{ form.wife_passport_attachment.name }} —
                            {{
                                formatBytes(form.wife_passport_attachment.size)
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('wife_passport_attachment')"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <label>{{ $t("wife_entry_stamp_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="
                        onFileChange($event, 'wife_entry_stamp_attachment')
                    "
                />
                <div v-if="form.wife_entry_stamp_attachment" class="file-list">
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
                            {{ form.wife_entry_stamp_attachment.name }} —
                            {{
                                formatBytes(
                                    form.wife_entry_stamp_attachment.size
                                )
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('wife_entry_stamp_attachment')"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <label>{{ $t("rent_contract_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="onFileChange($event, 'rent_contract_attachment')"
                />
                <div v-if="form.rent_contract_attachment" class="file-list">
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
                            {{ form.rent_contract_attachment.name }} —
                            {{
                                formatBytes(form.rent_contract_attachment.size)
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('rent_contract_attachment')"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <label>{{ $t("divorce_certificate_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="
                        onFileChange($event, 'divorce_certificate_attachment')
                    "
                />
                <div
                    v-if="form.divorce_certificate_attachment"
                    class="file-list"
                >
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
                            {{ form.divorce_certificate_attachment.name }} —
                            {{
                                formatBytes(
                                    form.divorce_certificate_attachment.size
                                )
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="
                                removeFile('divorce_certificate_attachment')
                            "
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <label>{{ $t("death_certificate_attachment") }}</label>
                <input
                    type="file"
                    class="form-control"
                    @change="
                        onFileChange($event, 'death_certificate_attachment')
                    "
                />
                <div v-if="form.death_certificate_attachment" class="file-list">
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
                            {{ form.death_certificate_attachment.name }} —
                            {{
                                formatBytes(
                                    form.death_certificate_attachment.size
                                )
                            }}
                        </span>
                        <button
                            type="button"
                            class="btn-delete small"
                            @click="removeFile('death_certificate_attachment')"
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
</template>

<script>
export default {
    data() {
        return {
            isLoading: true,
            governorates: [],
            errors: {},
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
                wife_full_name: "",
                wife_national_id: "",
                wife_main_phone: "",
                wife_additional_phone: "",
                wife_birth_date: "",
                wife_age: null,
                governorate_id: "",
                city_id: "",
                area_id: "",
                detailed_address: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                entry_date_to_egypt: "",
                entry_method: "",
                latitude: "",
                longitude: "",
                image: null,
                husband_name: "",
                husband_national_id: "",
                husband_location: "",
                wife_id_attachment: null,
                wife_passport_attachment: null,
                wife_entry_stamp_attachment: null,
                rent_contract_attachment: null,
                divorce_certificate_attachment: null,
                death_certificate_attachment: null,
                members: [
                    {
                        name: "",
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
        computedWifeAge() {
            if (!this.form.wife_birth_date) return "";
            const bd = new Date(this.form.wife_birth_date);
            const diff = Date.now() - bd.getTime();
            this.form.wife_age = Math.floor(
                diff / (1000 * 60 * 60 * 24 * 365.25)
            );
            return this.form.wife_age;
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
        addMember() {
            this.form.members.push({
                name: "",
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
        removeMember(i) {
            this.form.members.splice(i, 1);
        },
        memberAge(i) {
            const b = this.form.members[i].birth_date;
            if (!b) return "";
            const bd = new Date(b);
            const diff = Date.now() - bd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        updateMemberAge(i) {
            this.form.members[i].age = this.memberAge(i);
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
            });
            try {
                let fd = new FormData();
                for (let key in this.form) {
                    if (key !== "members") {
                        fd.append(key, this.form[key] || "");
                    }
                }
                this.form.members.forEach((member, i) => {
                    for (let mkey in member) {
                        let value = member[mkey];
                        if (typeof value === "boolean") {
                            value = value ? 1 : 0;
                        }
                        if (value instanceof File) {
                            fd.append(`members[${i}][${mkey}]`, value);
                        } else {
                            fd.append(`members[${i}][${mkey}]`, value || "");
                        }
                    }
                });
                const res = await axios.post(
                    "/users/humanitarian-cases/families-without-breadwinner/store",
                    fd,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );
                this.resetForm();
                this.$swal.fire({
                    allowOutsideClick: false,
                    title: this.$t("Success"),
                    text: this.$t("Case added successfully"),
                    icon: "success",
                });
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                    this.$swal.fire({
                        title: this.$t("Validation Error"),
                        text: this.$t("Please check the form and try again"),
                        icon: "warning",
                    });
                } else {
                    this.$swal.fire({
                        title: this.$t("Error"),
                        text:
                            error.response?.data?.message ||
                            this.$t("Something went wrong"),
                        icon: "error",
                    });
                }
            }
        },
        resetForm() {
            this.errors = {};
            this.form = {
                wife_full_name: "",
                wife_national_id: "",
                wife_main_phone: "",
                wife_additional_phone: "",
                wife_birth_date: "",
                wife_age: null,
                governorate_id: "",
                city_id: "",
                area_id: "",
                detailed_address: "",
                housing_status: "",
                rent_value: null,
                social_status: "",
                entry_date_to_egypt: "",
                entry_method: "",
                latitude: "",
                longitude: "",
                image: null,
                husband_name: "",
                husband_national_id: "",
                husband_location: "",
                wife_id_attachment: null,
                wife_passport_attachment: null,
                wife_entry_stamp_attachment: null,
                rent_contract_attachment: null,
                divorce_certificate_attachment: null,
                death_certificate_attachment: null,
                members: [
                    {
                        name: "",
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
            this.preview = null;
            this.captured = false;
            this.stopCamera();
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
.form-wrapper {
    padding: 15px;
    margin: 10px auto;
    background: #ffffff;
    box-sizing: border-box;
}
.title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
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
    padding: 4px;
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
.member-title {
    font-weight: 700;
    font-size: 1rem;
}
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
    display: flex;
    padding: 8px 10px;
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
button {
    margin: 4px;
}
</style>
