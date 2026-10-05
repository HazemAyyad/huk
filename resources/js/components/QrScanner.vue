<template>
    <div class="qr-container">
        <h2 class="text-xl font-bold mb-2 text-center">ماسح QR Code</h2>

        <button
            @click="toggleScanner"
            class="bg-blue-600 text-white px-4 py-2 rounded mb-4"
        >
            {{ scanning ? "إيقاف المسح" : "فتح الماسح" }}
        </button>

        <div v-if="scanning">
            <qrcode-stream @decode="onDecode" @init="onInit"></qrcode-stream>
        </div>

        <div v-if="decodedResult" class="mt-3 p-3 bg-green-100 rounded">
            <p><strong>الكود المقروء:</strong> {{ decodedResult }}</p>
            <button
                @click="sendToServer"
                class="bg-green-600 text-white px-3 py-2 rounded mt-2"
            >
                إرسال إلى Laravel
            </button>
        </div>

        <div v-if="error" class="text-red-600 mt-2">
            {{ error }}
        </div>
    </div>
</template>

<script>
import { QrcodeStream } from "vue-qrcode-reader";
import axios from "axios";

export default {
    name: "QrScanner",
    components: { QrcodeStream },
    data() {
        return {
            scanning: false,
            decodedResult: null,
            error: null,
        };
    },
    methods: {
        toggleScanner() {
            this.scanning = !this.scanning;
            if (!this.scanning) this.decodedResult = null;
        },

        onDecode(result) {
            this.decodedResult = result;
            this.scanning = false;
        },

        onInit(promise) {
            promise.catch((err) => {
                this.error = "تعذّر الوصول للكاميرا: " + err.message;
            });
        },

        async sendToServer() {
            if (!this.decodedResult) return;

            try {
                const response = await axios.post("/api/scan-result", {
                    code: this.decodedResult,
                });
                alert("تم الإرسال إلى Laravel بنجاح ✅");
            } catch (err) {
                this.error =
                    "خطأ أثناء الإرسال: " +
                    (err.response?.data?.message || err.message);
            }
        },
    },
};
</script>

<style scoped>
.qr-container {
    max-width: 400px;
    margin: auto;
    text-align: center;
}
</style>
