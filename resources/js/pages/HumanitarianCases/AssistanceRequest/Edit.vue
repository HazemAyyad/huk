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
                                {{ $t("Humanitarian cases") }}
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
                        {{ $t("تعديل طلب مساعدة") }}
                    </h2>
                </div>

                <div class="card-body">
                    <div
                        v-if="isLoading"
                        class="d-flex justify-content-center p-3"
                    >
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{
                                $t("جاري التحميل...")
                            }}</span>
                        </div>
                    </div>

                    <div v-else class="w-full mx-auto">
                        <form @submit.prevent="submit" class="form-wrapper">
                            <h3 class="section-title">
                                {{ $t("بيانات أساسية") }}
                            </h3>
                            <div class="grid">
                                <div class="field">
                                    <label>{{
                                        $t("عدد مرات الاستفادة")
                                    }}</label>
                                    <input
                                        v-model="form.benefit_count"
                                        :placeholder="$t('مثال: 4')"
                                    />
                                    <div
                                        v-if="errors.benefit_count"
                                        class="error-msg"
                                    >
                                        {{ errors.benefit_count[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("نوع المساعدة") }}</label>
                                    <input
                                        v-model="form.assistance_type"
                                        :placeholder="$t('نوع المساعدة')"
                                    />
                                    <div
                                        v-if="errors.assistance_type"
                                        class="error-msg"
                                    >
                                        {{ errors.assistance_type[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("اسم المستفيد / رب الأسرة")
                                    }}</label>
                                    <input v-model="form.beneficiary_name" />
                                    <div
                                        v-if="errors.beneficiary_name"
                                        class="error-msg"
                                    >
                                        {{ errors.beneficiary_name[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{
                                        $t("رقم الهوية / القومي")
                                    }}</label>
                                    <input
                                        v-model="form.beneficiary_nid"
                                        maxlength="14"
                                    />
                                    <div
                                        v-if="errors.beneficiary_nid"
                                        class="error-msg"
                                    >
                                        {{ errors.beneficiary_nid[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label
                                        >{{ $t("اسم الزوج / الزوجة") }}
                                        <small>(اختياري)</small></label
                                    >
                                    <input v-model="form.spouse_name" />
                                </div>

                                <div class="field">
                                    <label
                                        >{{ $t("رقم هوية الزوج/الزوجة") }}
                                        <small>(اختياري)</small></label
                                    >
                                    <input
                                        v-model="form.spouse_nid"
                                        maxlength="14"
                                    />
                                </div>

                                <div class="field">
                                    <label>{{ $t("تاريخ الميلاد") }}</label>
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
                                    <label>{{ $t("عدد أفراد الأسرة") }}</label>
                                    <input
                                        type="number"
                                        min="1"
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

                                <div class="field">
                                    <label>{{
                                        $t("تاريخ التقديم / الوصول")
                                    }}</label>
                                    <input
                                        type="date"
                                        v-model="form.request_date"
                                    />
                                </div>

                                <div class="field">
                                    <label
                                        >{{ $t("تاريخ بدء المساعدة") }}
                                        <small>(اختياري)</small></label
                                    >
                                    <input
                                        type="date"
                                        v-model="form.assistance_date"
                                    />
                                </div>

                                <div class="field full-width">
                                    <label>{{ $t("العنوان في مصر") }}</label>
                                    <textarea
                                        v-model="form.address"
                                        rows="2"
                                    ></textarea>
                                    <div
                                        v-if="errors.address"
                                        class="error-msg"
                                    >
                                        {{ errors.address[0] }}
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ $t("رقم الهاتف") }}</label>
                                    <input v-model="form.phone" />
                                </div>
                            </div>

                            <div class="footer">
                                <button type="submit" class="btn-save">
                                    {{ $t("حفظ طلب المساعدة") }}
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

            form: {
                benefit_count: "",
                assistance_type: "",
                beneficiary_name: "",
                beneficiary_nid: "",
                spouse_name: "",
                spouse_nid: "",
                birth_date: "",
                family_members_count: 1,
                request_date: "",
                assistance_date: "",
                address: "",
                phone: "",
                notes: "",
            },
        };
    },

    created() {
        this.fetchData();
    },

    methods: {
        async fetchData() {
            this.isLoading = true;

            try {
                const res = await axios.get(
                    `/admin/humanitarian-cases/assistance-request/${this.$route.params.id}/get-item`,
                );

                Object.assign(this.form, res.data);
            } catch (e) {
                this.$swal.fire("خطأ", "تعذر تحميل البيانات", "error");
            } finally {
                this.isLoading = false;
            }
        },

        async submit() {
            this.$swal.showLoading();

            try {
                const fd = new FormData();

                // إضافة كل الحقول العادية
                for (const [key, value] of Object.entries(this.form)) {
                    if (value instanceof File) {
                        fd.append(key, value);
                    } else if (value !== null && value !== undefined) {
                        fd.append(key, value);
                    }
                }

                fd.append("_method", "PATCH");

                const response = await axios.post(
                    `/admin/humanitarian-cases/assistance-request/${this.$route.params.id}/update`,
                    fd,
                );

                this.$swal.close();
                this.$swal.fire({
                    icon: "success",
                    title: "تم الحفظ بنجاح",
                    text: `تم حفظ طلب المساعدة:`,
                });

                this.resetForm();
                this.$router.push({
                    name: "admin.humanitarian.cases.assistance-request.index",
                });
            } catch (error) {
                this.$swal.close();

                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.$swal.fire(
                        "خطأ في البيانات",
                        "يرجى مراجعة الحقول المميزة باللون الأحمر",
                        "warning",
                    );
                } else {
                    this.$swal.fire(
                        "حدث خطأ",
                        "يرجى المحاولة مرة أخرى لاحقاً",
                        "error",
                    );
                }
            }
        },

        resetForm() {
            Object.assign(this.$data.form, this.$options.data().form);
            this.errors = {};
        },
    },
};
</script>

<style scoped>
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

/* footer and save button */
.footer {
    margin-top: 18px;
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

.error-msg {
    color: #dc3545;
    margin-top: 6px;
    font-size: 0.875rem;
    text-align: start;
}

button {
    margin: 4px;
}
</style>
