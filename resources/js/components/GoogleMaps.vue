<template>
    <div>
        <h3 class="mb-3">الحالات القريبة منك</h3>

        <div v-if="!userLocation">جاري تحديد موقعك...</div>

        <GMapMap
            v-else
            :center="userLocation"
            :zoom="13"
            style="width: 100%; height: 400px"
        >
            <!-- موقع المستخدم -->
            <GMapMarker
                :position="userLocation"
                icon="http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                title="موقعك"
            />

            <!-- الحالات القريبة -->
            <GMapMarker
                v-for="item in nearbyCases"
                :key="item.id"
                :position="{ lat: item.lat, lng: item.lng }"
                :icon="getMarkerIcon(item.type)"
                :title="`${item.name}`"
                @click="openCase(item)"
            />
        </GMapMap>
    </div>
</template>

<script>
export default {
    data() {
        return {
            userLocation: null,
            cases: [],
        };
    },

    mounted() {
        this.getUserLocation();
        this.getCases();
    },

    computed: {
        nearbyCases() {
            if (!this.userLocation) return [];

            return this.cases
                .map((item) => {
                    const distance = this.getDistance(
                        this.userLocation.lat,
                        this.userLocation.lng,
                        item.lat,
                        item.lng,
                    );

                    return {
                        ...item,
                        distance,
                    };
                })
        },
    },

    methods: {
        getMarkerIcon(type) {
            switch (type) {
                case 'amputation':
                    return 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'; // 🔵 بتر

                case 'guardian':
                    return 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'; //  🔴ولي أمر

                case 'patient':
                    return 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'; // 🟢 مرضى

                default:
                    return 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png'; // 🟡 باقي الحالات
            }
        },

        openCase(item) {
            const baseUrl = window.location.origin;

            window.location.href = `${baseUrl}/ar/admin/telegram/details/${item.id}?locale=ar`;
        },

        getCases() {
            axios.get("/admin/guardian-cases").then((res) => {
                this.cases = res.data.data;
            });
        },

        getUserLocation() {
            if (!navigator.geolocation) {
                this.$swal.fire({
                    icon: "error",
                    title: "المتصفح لا يدعم تحديد الموقع",
                });

                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                },
                (error) => {
                    this.$swal.fire({
                        icon: "error",
                        title: "لم يتم السماح بتحديد الموقع",
                    });

                    console.error(error);
                },
            );
        },

        // حساب المسافة بين نقطتين (Haversine)
        getDistance(lat1, lng1, lat2, lng2) {
            const toRad = (value) => (value * Math.PI) / 180;
            const R = 6371; // نصف قطر الأرض بالكيلومتر

            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);

            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) *
                    Math.cos(toRad(lat2)) *
                    Math.sin(dLng / 2) *
                    Math.sin(dLng / 2);

            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c;
        },
    },
};
</script>
