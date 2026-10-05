<template>
    <form @submit.prevent="submitReview" class="review-form">
        <div class="form-group">
            <label class="form-label"> اترك اسمك </label>

            <input
                type="text"
                v-model="form.name"
                class="form-control"
                placeholder="اترك اسمك"
            />

            <div v-if="errors.rating" class="error-text">
                {{ errors.rating[0] }}
            </div>
        </div>
        <!-- Rating -->
        <div class="form-group">
            <label class="form-label"> اترك تقييمك </label>

            <div class="stars">
                <span
                    v-for="star in 5"
                    :key="star"
                    @click="form.rating = star"
                    class="star"
                    :class="{ active: star <= form.rating }"
                >
                    ★
                </span>
            </div>

            <div v-if="errors.rating" class="error-text">
                {{ errors.rating[0] }}
            </div>
        </div>

        <!-- Message -->
        <div class="form-group">
            <label for="message" class="form-label"> اترك رسالتك </label>

            <textarea
                v-model="form.message"
                id="message"
                rows="4"
                placeholder="اترك رسالتك"
                class="form-control"
            ></textarea>

            <div v-if="errors.message" class="error-text">
                {{ errors.message[0] }}
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" :disabled="isSubmitting" class="submit-btn">
            {{ isSubmitting ? "جارى التحميل" : "ارسال" }}
        </button>
    </form>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            form: {
                rating: 0,
                message: "",
                name: "",
            },

            errors: {},
            isSubmitting: false,
        };
    },
    methods: {
        async submitReview() {
            this.isSubmitting = true;
            this.errors = {};

            try {
                await axios.post("/reviews/store", this.form);

                this.$swal.fire({
                    icon: "success",
                    title: "تم ارسال رسالتكم بنجاح",
                });

                this.form.rating = 0;
                this.form.message = "";
                this.form.name = "";
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                }
            } finally {
                this.isSubmitting = false;
            }
        },
    },
};
</script>

<style scoped>
.review-form {
    background: #ffffff;
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #ced4da;
    font-size: 0.95rem;
    outline: none;
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}

/* Stars */
.stars {
    display: flex;
    gap: 4px;
}

.star {
    font-size: 1.75rem;
    color: #adb5bd;
    cursor: pointer;
    transition:
        color 0.2s,
        transform 0.1s;
}

.star:hover {
    transform: scale(1.1);
}

.star.active {
    color: #ffc107;
}

/* Errors */
.error-text {
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: #dc3545;
}

/* Button */
.submit-btn {
    padding: 0.6rem 2.5rem;
    background: #0e0708;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 0.95rem;
    cursor: pointer;
    transition:
        background 0.2s,
        opacity 0.2s;
}

.submit-btn:hover {
    background: #000;
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
