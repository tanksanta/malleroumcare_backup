<?php
include_once("./_common.php");
?>

<style>
[x-cloak] {
    display: none;
}
</style>

<div class="antialiased sans-serif flex items-center justify-center" x-data="{ 'showModal': false }">
    <div x-data="app()" x-init="[initDate(), getNoOfDays()]" x-cloak>
        <div x-data="select({ value: 'all' })" x-init="init()" class="max-w-sm h-screen w-full shadow-lg flex flex-col">
            <!-- 상단 컨트롤 영역 -->
            <div class="basis-104 py-5 dark:bg-gray-800 bg-white"
                x-init="new Hammer($el).on('swipeleft swiperight', function(ev) {$dispatch(ev.type)})"
                @swipeleft="nextMonth()" @swiperight="prevMonth()">
                <!-- 헤더 -->
                <section class="px-0 flex items-center justify-center w-full">
                    <!-- 년/월 선택 -->
                    <div class="border rounded-lg px-1 flex flex-row p-1">
                        <button type="button"
                            class="leading-none rounded-lg transition ease-in-out duration-100 inline-flex cursor-pointer hover:bg-gray-200 p-1 items-center"
                            @click="month--; getNoOfDays()">
                            <svg class="h-6 w-6 text-gray-500 inline-flex leading-none" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="mx-2">
                            <span x-text="MONTH_NAMES[month]" class="text-lg font-bold text-gray-800"></span>
                            <span x-text="year" class="ml-1 text-lg text-gray-600 font-normal"></span>
                        </div>
                        <button type="button"
                            class="leading-none rounded-lg transition ease-in-out duration-100 inline-flex items-center cursor-pointer hover:bg-gray-200 p-1"
                            @click="month++; getNoOfDays()">
                            <svg class="h-6 w-6 text-gray-500 inline-flex leading-none" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- 일정표 관리 버튼 -->
                    <div class="basis-32 flex justify-center items-center" :class="{'hidden': mb_type !== 'manager'}">
                        <button
                            class="border rounded-lg px-2 py-1 flex justify-center items-center text-lg hover:bg-blue-100 transition-colors duration-300"
                            type="button" @click="showModal = mb_type === 'manager'" x-text="'일정표 관리'"></button>
                    </div>
                </section>

                <!-- 캘린더 -->
                <section class="items-center px-5 justify-between pt-8">
                    <div class="flex flex-wrap">
                        <template x-for="(day, index) in DAYS" :key="index">
                            <div style="width: 14.28%" class="px-2 py-2 relative">
                                <div x-text="day"
                                    class="text-gray-600 text-sm uppercase tracking-wide font-bold text-center"
                                    :class="{'text-red-400': day == '일', 'text-blue-400': day == '토'}">
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex flex-wrap w-full">
                        <template x-for="blankday in blankDays">
                            <div style="width: 14.28%; height: 40px;"
                                class="w-full flex justify-center text-base font-medium text-center text-gray-800 dark:text-gray-100">
                            </div>
                        </template>
                        <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                            <div style="width: 14.28%; height: 40px;"
                                class="px-2 pt-1 relative flex flex-col items-center">
                                <div @click="showEventModal(date)" x-text="date"
                                    class="inline-flex basis-6 w-6 h-6 items-center justify-center cursor-pointer text-center leading-none rounded-full transition ease-in-out duration-100"
                                    :class="{'bg-blue-500 text-white': isToday(date) == true, 'text-gray-700 hover:bg-blue-200': isToday(date) == false }">
                                </div>
                                <div class="flex-1 w-full flex justify-evenly items-center">
                                    <template
                                        x-for="(row, i) in Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())">
                                        <template x-for="(event, j) in events[row]">
                                            <div class="border-4 rounded-full"
                                                :class="{'hidden': filter_mb_id != '' && events[event].filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? e.od_b_name == filter_mb_id : e.partner_manager_mb_id == filter_mb_id).length == 0 }"
                                                :class="{'border-yellow-400': j % 3 === 0 && event.type === 'schedule', 'border-blue-400': j % 3 === 1 && event.type === 'schedule', 'border-purple-600': j % 3 === 2 && event.type === 'schedule', 'border-red-600': event.type === 'deny_schedule'}">
                                            </div>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <!-- 일정목록 -->
            <div class="flex-1 px-5 dark:bg-gray-700 bg-gray-50 overflow-y-auto">
                <div class="flex align-center font-bold text-lg md:py-8 py-5"
                    x-text="$moment(select_date).format('DD. dd')">
                </div>
                <template
                    x-for="(item, index) in filter_mb_id == '' ? schedules : schedules.filter(e => mb_type === 'default' ? e.od_b_name == filter_mb_id : e.partner_manager_mb_id == filter_mb_id)"
                    :key="index">
                    <div class="border-b py-2 border-gray-400 border-dashed">
                        <p class="text-xs leading-3 text-gray-500 dark:text-gray-300"
                            x-text="item.type === 'schedule' ? tConvert(item.delivery_datetime) + ' - ' + item.partner_manager_mb_name : item.partner_manager_mb_name">
                        </p>
                        <a tabindex="0"
                            class="focus:outline-none text-lg font-medium leading-5 text-gray-800 dark:text-gray-100 mt-2"
                            x-text="item.type === 'schedule' ? item.it_name : '설치 매니저 설치 불가'"></a>
                        <p class="text-sm pt-2 leading-4 leading-none text-gray-800 dark:text-gray-100"
                            x-text="'수령인 : ' + item.od_b_name"></p>
                        <p class="text-sm pt-2 leading-4 leading-none text-gray-800 dark:text-gray-100"
                            x-text="'배송지 : ' + item.od_b_addr1"></p>
                        <p class="text-sm pt-2 leading-4 leading-none text-gray-800 dark:text-gray-100"
                            x-text="'요청사항 : ' + item.od_memo"></p>
                    </div>

                </template>
            </div>

            <!-- 일정표 관리 모달 -->
            <div x-show="showModal" x-data="scheduleManager()" x-init="scheduleInit()"
                class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
                x-transition:enter="motion-safe:ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="motion-safe:ease-out duration-300"
                x-transition:leave-start="opacity-100 scale-90" x-transition:leave-end="opacity-0 scale-90">
                <div class="w-11/12 px-6 py-4 text-left bg-white rounded shadow-lg">
                    <!-- 모달 상단 -->
                    <div class="flex items-center justify-between">
                        <h5 class="mr-3 font-bold text-xl max-w-none">일정표 관리</h5>
                        <button type="button" class="z-50 cursor-pointer"
                            @click="showModal = false; check = false; schedule_deny_weeks = []; schedule_deny_days = ''; scheduleInit();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- 모달 내용 -->
                    <div class="h-8 flex justify-end items-center">
                        <label class="flex flex-row items-center px-4">
                            <span class="mr-2 text-lg font-bold" x-text="'이번 달만 적용'"></span>
                            <input type="checkbox" x-model="check">
                        </label>
                    </div>
                    <div class="h-32 flex flex-col mt-4">
                        <div class="basis-8 flex items-center font-bold text-xl">
                            설치(출고) 불가능 요일 설정
                        </div>
                        <div class="flex-1 border flex flex-col">
                            <div class="flex-1 border-b flex flex-row">
                                <template x-for="(day, index) in WEEKS" :key="index">
                                    <div class="flex-1 flex justify-center items-center text-lg font-bold bg-gray-100 border-r text-gray-600"
                                        :class="{ 'border-none' : WEEKS.length -1 == index, 'text-blue-400' : index == 5, 'text-red-400' : index == 6 }"
                                        x-text="day"></div>
                                </template>
                            </div>
                            <div class="flex-1 flex flex-row">
                                <template x-for="(day, index) in Object.values(WEEKS_COLLECTION)" :key="index">
                                    <div class="flex-1 flex justify-center items-center text-lg font-bold border-r cursor-pointer"
                                        :class="{ 'border-none' : WEEKS.length -1 == index }"
                                        @click="schedule_deny_weeks = onChangeDenyWeek(day)">
                                        <div class="w-6 h-6 rounded-full transition-colors duration-100"
                                            :class="{ 'bg-blue-400' : schedule_deny_weeks.includes(day), 'bg-white' : !schedule_deny_weeks.includes(day) }">
                                        </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="h-24 flex flex-col mt-4">
                        <div class="basis-8 flex items-center font-bold text-xl">설치(출고) 불가능 날짜 설정</div>
                        <div class="pt-4 flex-1 flex flex-row">
                            <input type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                x-model="schedule_deny_days" placeholder="설치 불가능한 날짜를 입력해주세요.(예시: 10,21)">
                        </div>
                    </div>
                    <div class="h-16 flex justify-center items-center">
                        <button
                            class="border rounded-lg px-8 py-1 flex justify-center items-center text-lg bg-blue-500 text-white font-bold transition-colors duration-300"
                            type="button"
                            @click="reload = req(calcDaysByMonth()); refetch(); showModal = false; scheduleInit();"
                            x-text="'등록'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function tConvert(time) {
        time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
        if (time.length > 1) {
            time = time.slice(1);
            time[5] = +time[0] < 12 ? ' AM' : ' PM';
            time[0] = +time[0] % 12 || 12;
        }
        return time.join('');
    }
    </script>

    <script>
    function req(list) {
        const data = {
            partner_mb_id: '<?php echo $member['mb_id']; ?>',
            partner_manager_mb_id: '<?php echo $_SESSION['ss_mb_id']; ?>',
            schedules: JSON.parse(JSON.stringify([...new Set(list)])),
        };
        let showModal = true;
        $.ajax('ajax.deny_schedule.php', {
            type: 'POST',
            cache: false,
            async: false,
            data,
            dataType: 'json',
            success: function(result) {
                if (result.data) checkSum = false;
            },
            error: function($xhr) {
                var message = $xhr.responseJSON.message;
                if (message) {
                    $('#code_keyup').text('* ' + message).css('color', '#d44747');
                    ret = message;
                } else {
                    $('#code_keyup').text('* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.').css('color',
                        '#333333');
                }
            }
        });
        return showModal;
    }
    </script>

    <script>
    function select(config) {
        let res;
        let mb_type;
        $.ajax('ajax.members.php', {
            type: 'POST',
            cache: false,
            async: false,
            data: {
                partner_mb_id: '<?php echo $_SESSION['ss_mb_id']; ?>'
            },
            dataType: 'json',
            success: function(result) {
                res = result.data.members;
                mb_type = result.data.mb_type;
            },
            error: function($xhr) {
                var message = $xhr.responseJSON.message;
                if (message) {
                    $('#code_keyup').text('* ' + message).css('color', '#d44747');
                    ret = message;
                } else {
                    $('#code_keyup').text('* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.').css('color',
                        '#333333');
                }
            }
        });
        return {
            data: res,
            emptyOptionsMessage: config.emptyOptionsMessage ?? '검색한 담당자가 존재하지 않습니다.',
            focusedOptionIndex: null,
            name: config.name,
            open: false,
            options: {},
            mb_type,
            placeholder: config.placeholder ?? '담당자 선택',
            search: '',
            value: config.value,
            filter_mb_id: '',
            closeListbox: function() {
                this.open = false
                this.focusedOptionIndex = null
                this.search = ''
            },
            focusNextOption: function() {
                if (this.focusedOptionIndex === null) return this.focusedOptionIndex = Object.keys(this
                        .options)
                    .length - 1
                if (this.focusedOptionIndex + 1 >= Object.keys(this.options).length) return
                this.focusedOptionIndex++
                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                    block: "center",
                })
            },
            focusPreviousOption: function() {
                if (this.focusedOptionIndex === null) return this.focusedOptionIndex = 0
                if (this.focusedOptionIndex <= 0) return
                this.focusedOptionIndex--
                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                    block: "center",
                })
            },
            init: function() {
                this.options = this.data
                if (!(this.value in this.options)) this.value = null
                this.$watch('search', ((value) => {
                    if (!this.open || !value) return this.options = this.data
                    this.options = Object.keys(this.data)
                        .filter((key) => this.data[key].toLowerCase().includes(value
                            .toLowerCase()))
                        .reduce((options, key) => {
                            options[key] = this.data[key]
                            return options
                        }, {})
                }))
            },
            selectOption: function() {
                if (!this.open) return this.toggleListboxVisibility()
                this.filter_mb_id = Object.keys(this.options)[this.focusedOptionIndex] == 'all' ? '' : Object
                    .keys(
                        this.options)[this.focusedOptionIndex];
                this.value = Object.keys(this.options)[this.focusedOptionIndex]
                this.closeListbox()
            },
            toggleListboxVisibility: function() {
                if (this.open) return this.closeListbox()
                this.focusedOptionIndex = Object.keys(this.options).indexOf(this.value)
                if (this.focusedOptionIndex < 0) this.focusedOptionIndex = 0
                this.open = true
                this.$nextTick(() => {
                    this.$refs.search.focus()
                    this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                        block: "nearest"
                    })
                })
            },
        }
    }
    </script>

    <script>
    const MONTH_NAMES = ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"];
    const DAYS = ["일", "월", "화", "수", "목", "금", "토"];

    function app() {
        const start = 1;
        const end = 10;
        let res;
        $.ajax('ajax.index.php', {
            type: 'POST',
            cache: false,
            async: false,
            data: {
                partner_mb_id: '<?php echo $_SESSION['ss_mb_id']; ?>'
            },
            dataType: 'json',
            success: function(result) {
                res = result.data;
            },
            error: function($xhr) {
                var message = $xhr.responseJSON.message;
                if (message) {
                    $('#code_keyup').text('* ' + message).css('color', '#d44747');
                    ret = message;
                } else {
                    $('#code_keyup').text('* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.').css('color',
                        '#333333');
                }
            }
        });

        return {
            month: "",
            year: "",
            no_of_days: [],
            blankDays: [],
            days: ["일", "월", "화", "수", "목", "금", "토"],
            events: res,
            select_date: new Date(),
            schedules: [],
            initDate: function() {
                const today = new Date();
                this.month = today.getMonth();
                this.year = today.getFullYear();
            },
            isToday: function(date) {
                const d = new Date(this.year, this.month, date);
                return this.select_date.toDateString() === d.toDateString() ? true : false;
            },
            showEventModal: function(date) {
                this.select_date = new Date(this.year, this.month, date);
                this.schedules = Object.keys(this.events).filter(e => new Date(e).toDateString() === new Date(this
                    .year,
                    this.month, date).toDateString()).length > 0 ? this.events[
                    `${this.year}-${((this.month+1) + '').padStart(2, '0')}-${(date+'').padStart(2, '0')}`] : [];
            },
            getNoOfDays: function() {
                let month = this.month + 0;
                let year = this.year + 0;
                if (month == 12) {
                    month = 0;
                    year += 1;
                } else if (month === -1) {
                    month = 11;
                    year -= 1;
                }
                let daysInMonth = new Date(year, month + 1, 0).getDate();
                let dayOfWeek = new Date(year, month).getDay();
                let blankDaysArray = [];
                for (var i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
                let daysArray = [];
                for (var i = 1; i <= daysInMonth; i++) daysArray.push(i);
                this.month = month;
                this.year = year;
                this.blankDays = blankDaysArray;
                this.no_of_days = daysArray;
            },
            nextMonth: function() {
                let month = this.month + 1;
                let year = this.year + 0;
                if (month == 12) {
                    month = 0;
                    year += 1;
                } else if (month === -1) {
                    month = 11;
                    year -= 1;
                }
                let daysInMonth = new Date(year, month + 1, 0).getDate();
                let dayOfWeek = new Date(year, month).getDay();
                let blankDaysArray = [];
                for (var i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
                let daysArray = [];
                for (var i = 1; i <= daysInMonth; i++) daysArray.push(i);
                this.month = month;
                this.year = year;
                this.blankDays = blankDaysArray;
                this.no_of_days = daysArray;
            },
            prevMonth: function() {
                let month = this.month - 1;
                let year = this.year + 0;
                if (month == 12) {
                    month = 0;
                    year += 1;
                } else if (month === -1) {
                    month = 11;
                    year -= 1;
                }
                let daysInMonth = new Date(year, month + 1, 0).getDate();
                let dayOfWeek = new Date(year, month).getDay();
                let blankDaysArray = [];
                for (var i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
                let daysArray = [];
                for (var i = 1; i <= daysInMonth; i++) daysArray.push(i);
                this.month = month;
                this.year = year;
                this.blankDays = blankDaysArray;
                this.no_of_days = daysArray;
            },
        };
    }
    </script>

    <script>
    const WEEKS = ["월", "화", "수", "목", "금", "토", "일"];
    const WEEKS_COLLECTION = {
        "월": 1,
        "화": 2,
        "수": 3,
        "목": 4,
        "금": 5,
        "토": 6,
        "일": 0
    };

    function scheduleManager() {
        moment.locale("ko");
        return {
            check: false,
            schedule_deny_days: '',
            schedule_deny_weeks: [], // 설치 불가능 요일 선택 목록
            onChangeDenyWeek: function(day) {
                let tmp = [].concat(this.schedule_deny_weeks);
                if (this.schedule_deny_weeks.includes(parseInt(day))) {
                    tmp = this.schedule_deny_weeks.filter(d => d != parseInt(day));
                } else {
                    tmp.push(parseInt(day));
                }
                this.schedule_deny_weeks = tmp;
                return tmp;
            },
            calcDaysByMonth: function() {
                const tmp = [];
                const month = (this.month + 1).toString();
                const year = this.year.toString();
                if (this.check) {
                    const beginDate = moment(`${year}-${month.replace("월", "")}-01`);
                    const endDate = moment(`${year}-${month.replace("월", "")}-01`).endOf('month');
                    const datesBetween = [];
                    let startingMoment = beginDate;
                    while (startingMoment <= endDate) {
                        datesBetween.push(moment(beginDate.format("YYYY-MM-DD")));
                        startingMoment.add(1, 'days');
                    }
                    // 요일로 체크
                    for (const i in datesBetween) {
                        if (this.schedule_deny_weeks.includes(parseInt(datesBetween[i].format('e')))) tmp.push(
                            datesBetween[i]
                            .format("YYYY-MM-DD"));
                    }
                    // 유효 날짜 체크
                    const days = this.schedule_deny_days.split(",");
                    for (const i in days) {
                        if (!isNaN(parseInt(days[i]))) {
                            const d = moment(
                                `${year}-${month.replace("월", "")}-${parseInt(days[i]).toString().padStart(2, '0')}`
                            );
                            if (d.isValid()) tmp.push(d.format("YYYY-MM-DD"));
                        }
                    }
                    return tmp;
                } else {
                    const beginDate = moment(`${year}-${month.replace("월", "")}-01`);
                    const endDate = moment(`${year}-12-31`);
                    const datesBetween = [];
                    let startingMoment = beginDate;
                    while (startingMoment <= endDate) {
                        datesBetween.push(moment(beginDate.format("YYYY-MM-DD")));
                        startingMoment.add(1, 'days');
                    }
                    // 요일로 체크
                    for (const i in datesBetween) {
                        if (this.schedule_deny_weeks.includes(parseInt(datesBetween[i].format('e')))) tmp.push(
                            datesBetween[i]
                            .format("YYYY-MM-DD"));
                    }
                    // 유효 날짜 체크
                    const days = this.schedule_deny_days.split(",");
                    for (const i in days) {
                        if (!isNaN(parseInt(days[i]))) {
                            const d = moment(
                                `${year}-${month.replace("월", "")}-${parseInt(days[i]).toString().padStart(2, '0')}`
                            );
                            if (d.isValid()) tmp.push(d.format("YYYY-MM-DD"));
                        }
                    }
                    return tmp;
                }
            },
            scheduleInit: function() {
                this.schedule_deny_weeks = [];
                this.schedule_deny_days = ""; // 사용자가 직접 입력한 설치 불가능 요일
                this.check = false;
            },
            refetch: function() {
                let res = [];
                $.ajax('ajax.index.php', {
                    type: 'POST',
                    cache: false,
                    async: false,
                    data: {
                        partner_mb_id: '<?php echo $_SESSION['ss_mb_id']; ?>'
                    },
                    dataType: 'json',
                    success: function(result) {
                        this.events = result.data;
                        this.schedule_deny_weeks = [];
                        this.schedule_deny_days = ""; // 사용자가 직접 입력한 설치 불가능 요일
                        this.check = false;
                    },
                    error: function($xhr) {
                        var message = $xhr.responseJSON.message;
                        if (message) {
                            $('#code_keyup').text('* ' + message).css('color', '#d44747');
                            ret = message;
                        } else {
                            $('#code_keyup').text('* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.').css(
                                'color',
                                '#333333');
                        }
                    }
                });
                return {
                    events: res,
                    showModal: false
                };
            }
        }
    }
    </script>
</div>