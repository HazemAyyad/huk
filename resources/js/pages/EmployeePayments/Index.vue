<template>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    {{ $t("manage_employee_payments") }}
                </h3>
            </div>

            <div class="card-body">
                <form @submit.prevent="addPayment">
                    <div class="row mb-3">
                        <div class="col-md-8 mx-auto">
                            <div class="form-group">
                                <label>
                                    {{ $t("select_employee") }}
                                </label>

                                <select
                                    v-model="form.user_id"
                                    class="form-control"
                                    required
                                >
                                    <option value="">
                                        {{ $t("choose_employee") }}
                                    </option>

                                    <option
                                        v-for="emp in employees"
                                        :key="emp.id"
                                        :value="emp.id"
                                    >
                                        {{ emp.name }}
                                        ({{ $t("basic_salary") }}:
                                        {{ emp.basic_salary }}
                                        {{ $t("egp") }})
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    {{ $t("amount") }}
                                </label>

                                <input
                                    type="number"
                                    v-model="form.amount"
                                    class="form-control"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                            </div>

                            <div class="form-group">
                                <label>
                                    {{ $t("payment_date") }}
                                </label>

                                <input
                                    type="date"
                                    v-model="form.payment_date"
                                    class="form-control"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 mx-auto">
                        <button
                            type="submit"
                            class="btn btn-success"
                            :disabled="loading"
                        >
                            <span v-if="loading">
                                {{ $t("processing") }}
                            </span>
                            <span v-else>
                                {{ $t("submit_payment") }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- قائمة الموظفين -->
        <div class="row mt-5">
            <div
                class="col-md-4 col-12 mb-3"
                v-for="emp in employees"
                :key="emp.id"
            >
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5>{{ emp.name }}</h5>

                        <p class="text-muted">
                            {{ $t("basic_salary") }}:
                            {{ emp.basic_salary }}
                            {{ $t("egp") }}
                        </p>

                        <router-link
                            :to="{
                                name: 'admin.payments.show',
                                params: { id: emp.id },
                            }"
                            class="btn btn-primary"
                        >
                            {{ $t("view_details") }}
                        </router-link>
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
            employees: [],
            form: {
                user_id: "",
                amount: "",
                payment_date: new Date().toISOString().split("T")[0],
            },
            loading: false,
            message: "",
        };
    },
    mounted() {
        this.fetchEmployees();
    },
    methods: {
        fetchEmployees() {
            axios
                .get("/admin/users/all")
                .then((response) => {
                    this.employees = response.data;
                })
                .catch((error) => {
                    alert("خطأ في تحميل الموظفين");
                    console.error(error);
                });
        },
        addPayment() {
            this.loading = true;
            this.message = "";

            axios
                .post("/admin/employee-payments/store", this.form)
                .then((response) => {
                    this.message = response.data.message;
                    this.form.amount = "";
                    this.form.user_id = "";

                    this.fetchEmployees();
                })
                .catch((error) => {
                    alert("حدث خطأ أثناء التسجيل");
                    console.error(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style scoped>
.card {
    border-radius: 10px;
}
</style>
