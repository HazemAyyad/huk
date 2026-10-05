<template>
    <div class="container mt-5" v-if="employee">
        <div class="card shadow">
            <div
                class="card-header bg-info text-white d-flex justify-content-between align-items-center"
            >
                <h3 class="mb-0">تفاصيل الموظف: {{ employee.name }}</h3>
                <!-- <button
                    @click="downloadPdf"
                    class="btn btn-light"
                    :disabled="pdfLoading"
                >
                    <span v-if="pdfLoading">جاري التحميل...</span>
                    <span v-else>تحميل تقرير PDF</span>
                </button> -->
            </div>
            <div class="card-body">
                <p>
                    <strong>الراتب الأساسي:</strong>
                    {{ employee.basic_salary }} جنيه
                </p>

                <h4 class="mt-4">الدفعات المسجلة</h4>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>التاريخ</th>
                            <th>القيمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in payments" :key="payment.id">
                            <td>{{ formatDate(payment.payment_date) }}</td>
                            <td>{{ payment.amount }} جنيه</td>
                        </tr>
                        <tr v-if="payments.length === 0">
                            <td colspan="2" class="text-center">
                                لا توجد دفعات
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h4 class="mt-5">الشهور المغطاة</h4>
                <table class="table table-bordered">
                    <thead class="table-success">
                        <tr>
                            <th>الشهر</th>
                            <th>الحالة</th>
                            <th>المبلغ المدفوع (لو جزئي)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="paid in paidSalaries" :key="paid.id">
                            <td>{{ formatMonth(paid.month) }}</td>
                            <td>
                                <span
                                    class="badge"
                                    :class="
                                        paid.status === 'full'
                                            ? 'bg-success'
                                            : 'bg-warning'
                                    "
                                >
                                    {{
                                        paid.status === "full" ? "كامل" : "جزئي"
                                    }}
                                </span>
                            </td>
                            <td>
                                {{
                                    paid.partial_amount > 0
                                        ? paid.partial_amount + " جنيه"
                                        : "-"
                                }}
                            </td>
                        </tr>
                        <tr v-if="paidSalaries.length === 0">
                            <td colspan="3" class="text-center">
                                لا توجد شهور مغطاة بعد
                            </td>
                        </tr>
                    </tbody>
                </table>

                <router-link
                    :to="{ name: 'admin.payments.index' }"
                    class="btn btn-secondary mt-3"
                >
                    رجوع
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            employee: null,
            payments: [],
            paidSalaries: [],
            pdfLoading: false,
        };
    },
    mounted() {
        this.fetchEmployeeDetail();
    },
    methods: {
        fetchEmployeeDetail() {
            const id = this.$route.params.id;
            axios
                .get(`/admin/employee-payments/${id}/get-item`)
                .then((response) => {
                    this.employee = response.data.employee;
                    this.payments = response.data.payments;
                    this.paidSalaries = response.data.paidSalaries;
                })
                .catch((error) => {
                    alert("موظف غير موجود");
                    this.$router.push({ name: "admin.payments.index" });
                });
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString("ar-EG");
        },

        formatMonth(date) {
            return new Date(date).toLocaleDateString("ar-EG", {
                year: "numeric",
                month: "long",
            });
        },
    },
};
</script>
