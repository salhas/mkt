<script setup>
import { ref } from 'vue';

const props = defineProps({
    initialWeather: Object,
});

const weatherData = ref(props.initialWeather || null);
const isLoading = ref(false);
const selectedLocation = ref(weatherData.value?.location?.code || '73.71.01.1001');

const changeLocation = async (e) => {
    const code = e.target.value;
    isLoading.value = true;
    try {
        const response = await fetch(`/dashboard/weather?code=${code}`);
        if (response.ok) {
            const data = await response.json();
            weatherData.value = data;
        }
    } catch (err) {
        console.error('Failed to fetch weather from BMKG:', err);
    } finally {
        isLoading.value = false;
    }
};

// Alert status if weather is rain or thunderstorm
const isSevereWeather = (desc = '') => {
    const d = desc.toLowerCase();
    return d.includes('hujan lebat') || d.includes('petir') || d.includes('ekstrem') || d.includes('badai');
};
</script>

<template>
    <div v-if="weatherData" class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-6">
        <!-- Widget Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-500 via-amber-500 to-orange-500 text-white flex items-center justify-center font-extrabold text-lg shadow-md shrink-0">
                    🌤️
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Prakiraan Cuaca BMKG</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60">
                            {{ weatherData.source }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">Pemantauan iklim & potensi bencana di Posko MKT</p>
                </div>
            </div>

            <!-- Location Selector & Status -->
            <div class="flex items-center space-x-3 shrink-0">
                <div class="relative">
                    <select
                        :value="selectedLocation"
                        @change="changeLocation"
                        :disabled="isLoading"
                        class="py-2 pl-3 pr-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-semibold text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-brand-500 transition disabled:opacity-50"
                    >
                        <option
                            v-for="loc in weatherData.available_locations"
                            :key="loc.code"
                            :value="loc.code"
                        >
                            📍 {{ loc.label }}
                        </option>
                    </select>
                    <span v-if="isLoading" class="absolute right-3 top-2.5 w-3 h-3 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></span>
                </div>
            </div>
        </div>

        <!-- Weather Main Banner Card (Primary Soft Orange Theme) -->
        <div class="relative rounded-3xl bg-gradient-to-br from-brand-500 via-amber-500 to-orange-600 text-white p-6 md:p-8 shadow-xl overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <!-- Left: Big Temp & Condition -->
                <div class="space-y-2 md:col-span-2">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold text-white">
                        <span>📍 {{ weatherData.location.full }}</span>
                        <span>•</span>
                        <span>Update {{ weatherData.updated_at }}</span>
                    </div>

                    <div class="flex items-center space-x-4 pt-2">
                        <div class="w-20 h-20 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center p-2 shrink-0 shadow-inner">
                            <img
                                :src="weatherData.current.icon"
                                :alt="weatherData.current.desc"
                                class="w-full h-full object-contain filter drop-shadow-md"
                                @error="(e) => e.target.src = 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg'"
                            />
                        </div>
                        <div>
                            <div class="text-4xl md:text-5xl font-black tracking-tight leading-none">
                                {{ weatherData.current.temp }}<span class="text-3xl font-light">°C</span>
                            </div>
                            <h3 class="text-lg font-bold mt-1 text-amber-100">
                                {{ weatherData.current.desc }}
                            </h3>
                            <p class="text-xs text-amber-200">
                                Angin {{ weatherData.current.wind }} km/jam ({{ weatherData.current.wind_direction }})
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Weather Metrics Cards -->
                <div class="grid grid-cols-2 gap-3 bg-black/15 p-4 rounded-2xl backdrop-blur-md border border-white/10 text-xs">
                    <div class="space-y-0.5">
                        <span class="text-amber-100 text-[10px] uppercase font-bold block">Kelembapan</span>
                        <span class="text-base font-extrabold">{{ weatherData.current.humidity }}%</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-amber-100 text-[10px] uppercase font-bold block">Kecepatan Angin</span>
                        <span class="text-base font-extrabold">{{ weatherData.current.wind }} km/j</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-amber-100 text-[10px] uppercase font-bold block">Arah Angin</span>
                        <span class="text-base font-extrabold">{{ weatherData.current.wind_direction }}</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-amber-100 text-[10px] uppercase font-bold block">Status BMKG</span>
                        <span class="text-xs font-bold text-emerald-300">Siaga 24 Jam</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Severe Weather Alert Banner if rain / thunderstorm forecast -->
        <div v-if="isSevereWeather(weatherData.current.desc)" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 flex items-center space-x-3">
            <span class="text-xl">⛈️</span>
            <div class="text-xs">
                <span class="font-extrabold block">Peringatan Cuaca Ekstrem BMKG</span>
                <span>Terdeteksi {{ weatherData.current.desc }} di wilayah {{ weatherData.location.name }}. Tim Rescue MKT disiagakan untuk penanggulangan bencana darurat.</span>
            </div>
        </div>

        <!-- Hourly & Daily Forecast Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Hourly Forecast (Prakiraan Jam) -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center justify-between">
                    <span>Prakiraan Per Jam (Hari Ini)</span>
                    <span class="text-[10px] text-gray-400 font-normal">Interval 3 Jam BMKG</span>
                </h4>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <div
                        v-for="(slot, i) in weatherData.hourly"
                        :key="i"
                        class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-center space-y-1 hover:bg-brand-50/50 hover:border-brand-200 dark:hover:bg-brand-950/30 transition"
                    >
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 block">{{ slot.time }}</span>
                        <div class="w-8 h-8 mx-auto p-0.5">
                            <img :src="slot.icon" :alt="slot.desc" class="w-full h-full object-contain" />
                        </div>
                        <span class="text-sm font-black text-gray-800 dark:text-gray-100 block">{{ slot.temp }}°C</span>
                        <span class="text-[9px] text-gray-400 block truncate">{{ slot.desc }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: 3-Day Outlook (Proyeksi 3 Hari) -->
            <div class="space-y-3">
                <h4 class="text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Proyeksi 3 Hari Kemanusiaan
                </h4>
                <div class="space-y-2">
                    <div
                        v-for="(day, i) in weatherData.daily"
                        :key="i"
                        class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs hover:border-brand-200 transition"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-950/60 border border-brand-100 dark:border-brand-900/40 p-1 shrink-0 flex items-center justify-center">
                                <img :src="day.icon" :alt="day.desc" class="w-full h-full object-contain" />
                            </div>
                            <div>
                                <span class="font-bold text-gray-800 dark:text-gray-100 block">{{ day.day }}</span>
                                <span class="text-[10px] text-gray-400">{{ day.desc }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-gray-800 dark:text-gray-100">{{ day.max_temp }}°C</span>
                            <span class="text-gray-400 text-[10px] block">{{ day.min_temp }}°C min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
