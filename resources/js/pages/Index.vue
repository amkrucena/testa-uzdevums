<script setup lang="ts">
import Layout from '../layouts/Default.vue'
import {PropType, ref, watch} from 'vue';
import {router} from "@inertiajs/vue3";

const props = defineProps({
    dates: {
        type: Array as PropType<string[]>,
        required: true,
    },
    rates: {
        type: Object,
        required: true,
    },
    currencies: {
        type: Array as PropType<string[]>,
        required: true,
    },
    availableDates: {
        type: Array as PropType<string[]>,
        required: true,
    },
    errors: {
        type: Array as PropType<string[]>,
        required: false
    },
    defaultDateRange: {
        type: Number,
        required: false,
    }
})

const searchParams = new URLSearchParams(window.location.search);
const startDate = searchParams.get('startDate');
const endDate = searchParams.get('endDate');

const defaultStartDate = new Date();
defaultStartDate.setDate(defaultStartDate.getDate() - (props.defaultDateRange ?? 10));

const range = ref({
    start: startDate ? new Date(startDate) : defaultStartDate,
    end: endDate ? new Date(endDate) : new Date(),
});

const queryCurrencies = searchParams.get('currencies');
const selectedCurrencies = ref<string[]>(queryCurrencies ? queryCurrencies.split(',') : []);

const formattedDates = props.availableDates?.map((date) => {
    return {
        key: 'available',
        content: 'gray',
        dates: new Date(date),
    };
});

const attributes = ref([
    {
        key: 'today',
        highlight: {
            color: 'green',
            fillMode: 'light',
        },
        dates: new Date(),
    },
    {
        key: 'empty',
        content: 'red',
        dates: {
            start: props.availableDates[ props.availableDates?.length - 1 ],
            end: props.availableDates[0]
        },
    },
    ...formattedDates
]);


watch([selectedCurrencies, range], ([newCurrency, newDates]) => {
    const currencyString = newCurrency.join(',');
    const startDateString = newDates.start.toISOString().split('T')[0];
    const endDateString = newDates.end.toISOString().split('T')[0];

    searchParams.set('currencies', currencyString);
    searchParams.set('startDate', startDateString);
    searchParams.set('endDate', endDateString);
    const newUrl = window.location.pathname + '?' + searchParams.toString();

    router.get(newUrl);
});
</script>

<template>
    <Layout>
        <div class="container m-auto">
            <div
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-5 mx-4"
                 role="alert"
                v-for="error in errors"
            >
                <strong class="font-bold">Holy smokes! </strong>
                <span class="block sm:inline">{{error}}</span>
            </div>
            <div class="flex max-md:flex-col-reverse auto-cols-max gap-10 w-full">
                <div class="filters w-full md:max-w-64">
                    <div class="grid grid-flow-row auto-rows-max gap-4 px-4">
                        <div>
                            <select
                                multiple
                                id="currency"
                                v-model="selectedCurrencies"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full h-52 p-2.5"
                            >
                                <option :value="currency" v-for="currency in currencies">{{ currency }}</option>
                            </select>
                        </div>

                        <div>
                            <VDatePicker
                                expanded
                                v-model.range="range"
                                mode="date"
                                timezone="Europe/Riga"
                                :min-date="availableDates[availableDates.length - 1]"
                                :max-date="new Date(availableDates[0])"
                                :attributes="attributes"
                            />

                            <p class="text-sm text-red-500 py-2"> *Red dates don't have data!</p>
                        </div>

                        <a href="/"
                           class="text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                        >
                            Clear
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden px-4 w-full">
                    <div class="rates mm-8 overflow-auto h-[400px] md:h-[calc(100vh-250px)] w-full">
                        <table class="border-collapse border text-sm">
                            <thead>
                            <tr class="sticky top-0 bg-slate-200 z-10">
                                <th class="border-b p-4 text-left sticky left-0 bg-slate-200 z-10  font-bold">Currency
                                </th>
                                <th
                                    class="border-b p-4 text-right whitespace-nowrap font-bold"
                                    v-for="date in dates"
                                    :key="date"
                                >
                                    {{ date }}
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white">
                            <tr class="hover:bg-slate-100"
                                v-for="(rate, currency) in rates"
                                :key="currency"
                            >
                                <td class="border-b border-slate-100 p-4 sticky left-0 bg-slate-200 whitespace-nowrap font-bold">
                                    {{ currency }}
                                </td>
                                <td
                                    class="border-b border-slate-100 p-4 text-right"
                                    v-for="date in dates"
                                    :key="date"
                                >
                                    {{ rate[date] }}
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </Layout>
</template>
