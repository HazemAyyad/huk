<template></template>

<script>
export default {
    data() {
        return {};
    },
    methods: {
        async startCamera() {
            this.error = null;
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user" },
                });
                this.videoStream = stream;
                this.$refs.video.srcObject = stream;
                this.streaming = true;
            } catch (err) {
                this.error = "Error: " + err.message;
            }
        },

        takePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.preview = canvas.toDataURL("image/jpeg", 0.9);
            this.captured = true;
            this.stopCamera();
        },

        retake() {
            this.preview = null;
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
    },

    beforeUnmount() {
        this.stopCamera();
    },
};
</script>

<style scoped>
button {
    margin: 4px;
}
</style>
