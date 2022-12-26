<?php
include_once("./_common.php");
?>

<div class="antialiased sans-serif bg-gray-100 max-h-screen" x-data="global()">
  <div x-data="app()" x-init="[initDate(), getNoOfDays()]" x-cloak class="px-4 py-2">
    <div x-data="select({ value: 'all', valueInModal: '' })" x-init="init()"
      class="flex flex-col bg-white rounded-lg shadow overflow-hidden">
      <!-- 상단 컨트롤 영역 -->
      <div class="flex items-center justify-between py-2 px-6">
        <!-- 담당자 선택 -->
        <div class="basis-72 flex flex-row">
          <div class="basis-36 flex justify-center items-center text-lg font-bold">
            <?php
                        if ($member["mb_type"] == "partner" || $member["mb_type"] == "manager") {
                            echo '담당자';
                        } else {
                            if ($member["mb_level"] >= 9) {
                                echo '설치파트너';
                            } else {
                                echo '수급자';
                            }
                        }
                        ?>
          </div>
          <div class="flex-1 flex">
            <div @click.away="closeListbox()" @keydown.escape="closeListbox()" class="relative w-full">
              <span class="inline-block w-full rounded-md shadow-sm">
                <button x-ref="button" @click="toggleListboxVisibility()" :aria-expanded="open" aria-haspopup="listbox"
                  class="relative z-0 w-full py-2 pl-3 pr-10 text-left transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md cursor-default focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5">
                  <span id="select_filter" x-show="! open" x-text="value in options ? options[value] : placeholder"
                    :class="{ 'text-gray-500': ! (value in options) }" class="block truncate"></span>

                  <input x-ref="search" x-show="open" x-model="search" @keydown.enter.stop.prevent="selectOption()"
                    @keydown.arrow-up.prevent="focusPreviousOption()" @keydown.arrow-down.prevent="focusNextOption()"
                    type="search" class="w-full h-full p-0 form-control focus:outline-none" />

                  <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                      <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                    </svg>
                  </span>
                </button>
              </span>

              <div x-show="open" x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg">
                <ul x-ref="listbox" @keydown.enter.stop.prevent="selectOption()"
                  @keydown.arrow-up.prevent="focusPreviousOption()" @keydown.arrow-down.prevent="focusNextOption()"
                  role="listbox"
                  :aria-activedescendant="focusedOptionIndex ? name + 'Option' + focusedOptionIndex : null"
                  tabindex="-1"
                  class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                  <template x-for="(key, index) in Object.keys(options)" :key="index">
                    <li :id="name + 'Option' + focusedOptionIndex" @click="selectOption()"
                      @mouseenter="focusedOptionIndex = index" @mouseleave="focusedOptionIndex = null" role="option"
                      :aria-selected="focusedOptionIndex === index"
                      :class="{ 'text-white bg-blue-600': index === focusedOptionIndex, 'text-gray-900': index !== focusedOptionIndex }"
                      class="relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9">
                      <span x-text="Object.values(options)[index]"
                        :class="{ 'font-semibold': index === focusedOptionIndex, 'font-normal': index !== focusedOptionIndex }"
                        class="block font-normal truncate"></span>

                      <span x-show="key === value"
                        :class="{ 'text-white': index === focusedOptionIndex, 'text-blue-600': index !== focusedOptionIndex }"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                        </svg>
                      </span>
                    </li>
                  </template>

                  <div x-show="! Object.keys(options).length" x-text="emptyOptionsMessage"
                    class="px-3 py-2 text-gray-900 cursor-default select-none"></div>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- 년/월 선택 -->
        <div class="border rounded-lg px-1 flex flex-row p-1">
          <button type="button"
            class="leading-none rounded-lg transition ease-in-out duration-100 inline-flex items-center cursor-pointer hover:bg-gray-200 p-1 transition-colors duration-300"
            @click="month--; getNoOfDays()">
            <svg class="h-6 w-6 text-gray-500 inline-flex leading-none" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <div class="mx-2">
            <span x-text="MONTH_NAMES[month]" class="text-lg font-bold text-gray-800"></span>
            <span x-text="year" class="ml-1 text-lg text-gray-600 font-normal"></span>
          </div>
          <button type="button"
            class="leading-none rounded-lg transition ease-in-out duration-100 inline-flex items-center cursor-pointer hover:bg-gray-200 p-1 transition-colors duration-300"
            @click="month++; getNoOfDays()">
            <svg class="h-6 w-6 text-gray-500 inline-flex leading-none" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- 설치불가일 관리 버튼 -->
        <div class="basis-40 flex justify-center items-center">
          <button
            class="border rounded-lg px-4 py-1 flex justify-center items-center text-base hover:bg-blue-100 transition-colors duration-300"
            type="button" x-show="mb_type === 'manager' || mb_type === 'partner'"
            @click="showModal = (mb_type === 'manager' || mb_type === 'partner')" x-text="'설치불가일 관리'"></button>
        </div>

        <!-- 창 닫기 버튼 -->
        <div class="flex items-center justify-center">
          <button type="button"
            class="leading-none rounded-full transition ease-in-out duration-100 inline-flex items-center cursor-pointer hover:bg-gray-100 p-1 transition-colors duration-300"
            :class="{'cursor-not-allowed opacity-25': month == 11 }" onclick="window.close();">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- 메인 컨트롤 영역 -->
      <div class="flex flex-column border-t">
        <!-- 캘린더 컨트롤 영역 -->
        <section class="flex-1 mr-4"
          x-init="new Hammer($el).on('swipeleft swiperight', function(ev) {$dispatch(ev.type)})"
          @swipeleft="nextMonth()" @swiperight="prevMonth()">
          <div class="-mx-1 -mb-1">
            <div class="flex flex-wrap border-l">
              <template x-for="(day, index) in DAYS" :key="index">
                <div style="width: 14.28%" class="px-2 py-2 border-r relative">
                  <div x-text="day" class="text-gray-600 text-sm uppercase tracking-wide font-bold text-center"
                    :class="{'text-red-400': day == '일', 'text-blue-400': day == '토'}">
                  </div>
                </div>
              </template>
            </div>

            <div class="flex flex-wrap border-t border-l">
              <template x-for="blankday in blankDays">
                <div style="width: 14.28%; height: 112px" class="text-center border-r border-b px-4 pt-2">
                </div>
              </template>
              <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                <div style="width: 14.28%; height: 112px" class="px-2 pt-1 border-r border-b relative flex flex-col"
                  :class="{'bg-gray-100': moment([year, month, date]).diff(moment(), 'days') < 0}">
                  <div class="basis-6 flex flex-row">
                    <div @click="showEventModal(date)" x-text="date"
                      class="basis-6 inline-flex w-6 h-6 items-center justify-center cursor-pointer text-center leading-none rounded-full transition ease-in-out duration-100"
                      :class="{'bg-blue-500 text-white': isToday(date) == true, 'text-gray-700 hover:bg-blue-200': isToday(date) == false }">
                    </div>

                  </div>
                  <div class="flex-1">
                    <div style="height: 80px" class="overflow-y-auto">
                      <!-- 설치 불가 일정 -->
                      <div class="flex-1 flex flex-row" :class="{'hidden': Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString()).length === 0 ? true : 
                                                    events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule').length === 0 ? true : 
                                                    (
                                                        (filter_mb_id !== '' && events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule').length === 0) ||
                                                        (filter_mb_id !== '' && events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id).length === 0)
                                                    )}">
                        <div class="flex flex-col justify-center w-full max-h-6">
                          <div class="h-4 flex items-center flex-row">
                            <div class="h-2 border-4 rounded-full border-red-600"></div>
                            <span class="ml-1 text-xs line-clamp-1" x-text="
                                                            Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString()).length !== 0 ?
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule').length > 1 ?
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule')[0]?.partner_manager_mb_name + ' 외 ' + (events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule').length - 1) + '건' :
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'deny_schedule')[0]?.partner_manager_mb_name : 
                                                            ''"></span>
                          </div>
                        </div>
                      </div>
                      <!--  설치 일정  -->
                      <div class="flex-1 flex flex-row" :class="{'hidden': Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString()).length === 0 ? true : 
                                                    events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').length === 0 ? true : 
                                                    (
                                                        (filter_mb_id !== '' && events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').length === 0) ||
                                                        (filter_mb_id !== '' && events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id).length === 0)
                                                    )}">
                        <div class="flex flex-col justify-center w-full max-h-6">
                          <div class="h-4 flex items-center flex-row">
                            <div class="h-2 border-4 rounded-full border-blue-600"></div>
                            <span class="ml-1 text-xs line-clamp-1" x-text="filter_mb_id == '' ? 
                                                            Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString()).length !== 0 ?
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').length > 1 ?
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule')[0]?.it_name.slice(0, 6) + '... 외 ' + (events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').length - 1) + '건' :
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule')[0]?.it_name.slice(0, 6) + '...' : 
                                                            '' : 
                                                            Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString()).length !== 0 ? 
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').length > 1 && events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id).length > 1 ?
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id)[0]?.it_name.slice(0, 6) + '... 외 ' + (events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id).length - 1) + '건' :
                                                            events[Object.keys(events).filter(e => new Date(e).toDateString() === new Date(year, month, date).toDateString())[0]].filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id)[0]?.it_name.slice(0, 6) + '...' :
                                                            ''"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </section>

        <!-- 상세 정보 컨트롤 영역 -->
        <section class="basis-80 bg-white border-l flex flex-col h-172">
          <!-- 상세 정보 요약 영역 -->
          <div class="basis-24 flex flex-col justify-center px-4 pt-4 border-b">
            <h3 x-text="$moment(select_date).format('YYYY-MM-DD (dd)')" class="text-xl font-bold" />
          </div>

          <!-- 상세 정보 스크롤 뷰 영역 -->
          <ul class="flex-1 overflow-y-auto" id="table">
            <template
              x-for="(item, index) in filter_mb_id == '' ? schedules.filter(e => e.type == 'deny_schedule') : schedules.filter(e => e.type == 'deny_schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id)"
              :key="index">
              <li class="min-h-64 flex flex-col px-4">
                <div class=" basis-12 flex flex-col align-center px-4" :class="{'hidden' : index !== 0}">
                  <p class="flex-1 inline-flex items-center text-lg font-bold" x-text="'설치 불가 담당자'" />
                </div>
                <div class="flex-1 flex flex-col border" :id="index" :data-partner-mb-id="item.partner_mb_id"
                  :data-partner-manager-mb-id="item.partner_manager_mb_id" @click.prevent="doubleClick">
                  <div class="flex-1 flex flex-row items-center justify-center">
                    <span x-text="item.partner_manager_mb_name" />
                  </div>
                </div>
              </li>
            </template>
            <template
              x-for="(item, index) in filter_mb_id == '' ? schedules.filter(e => e.type == 'schedule') : schedules.filter(e => e.type === 'schedule').filter(e => mb_type === 'default' ? <?php if ($member["mb_type"] === "default" && $member["mb_level"] >= 9) echo 'e.partner_mb_id'; else echo 'e.od_b_name'; ?> == filter_mb_id : e.partner_manager_mb_id === filter_mb_id)"
              :key="index">
              <li class="min-h-64 flex flex-col mb-4 px-4">
                <div class="basis-12 flex flex-row align-center">
                  <p class="flex-1 inline-flex items-center text-lg font-bold"
                    x-text="(item.status === '완료' || item.status === '작성') ? '설치완료' : '설치예정 : ' + item.delivery_datetime" />
                  <div class="basis-30 flex items-center justify-start">
                    <button type="button"
                      class="border rounded-lg px-2 py-1 flex justify-center items-center text-base hover:bg-blue-100 transition-colors duration-300"
                      @click="goToUrl(item.od_id)" x-text="'설치결과보고서'">
                    </button>
                  </div>
                </div>

                <div class="flex-1 flex flex-col border">
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold" x-text="'상품명'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2 border-r">
                      <span x-text="item.it_name" />
                    </div>
                    <div class="basis-10 flex justify-center items-center">
                      <span x-text="(item.ct_qty ? item.ct_qty : 0) + '개'" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class=" font-bold"
                        x-text="<?php if ($member["mb_level"] < 9 && $member["mb_type"] === 'default') echo 'true'; else echo 'false'; ?> ? '설치담당자' : '담당업체'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.partner_manager_mb_name" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b"
                    :class="{'hidden': <?php if ($member["mb_level"] < 9 && $member["mb_type"] === 'default') echo 'false'; else echo 'true'; ?>">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold"
                        x-text="'<?php if ($member["mb_level"] < 9 && $member["mb_type"] === 'default') echo '담당연락처'; else echo '업체연락처'; ?>'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.partner_hp ? item.partner_hp : '없음'" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold" x-text="'수령인'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.od_b_name" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold" x-text="'연락처'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.od_b_hp" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold" x-text="'배송주소'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.od_b_addr1 + (item.od_b_addr2 ? item.od_b_addr2 :'')" />
                    </div>
                  </div>
                  <div class="flex-1 flex flex-row border-b">
                    <div class="basis-24 min-w-24 flex pl-2 justify-start items-center border-r bg-gray-100">
                      <span class="font-bold" x-text="'요청사항'" />
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2">
                      <span x-text="item.prodMemo" />
                    </div>
                  </div>
                </div>
              </li>
            </template>
          </ul>
        </section>
      </div>

      <!-- 설치불가일 관리 모달 -->
      <div x-show="showModal" x-data="scheduleManager()" x-init="scheduleInit()"
        class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
        x-transition:enter="motion-safe:ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="motion-safe:ease-out duration-300"
        x-transition:leave-start="opacity-100 scale-90" x-transition:leave-end="opacity-0 scale-90">
        <div class="max-w-3xl px-6 py-4 mx-auto text-left bg-white rounded shadow-lg">
          <!-- 모달 상단 -->
          <div class="flex items-center justify-between">
            <h5 class="mr-3 font-bold text-xl max-w-none">설치불가일 등록</h5>
            <button type="button" class="z-50 cursor-pointer"
              @click="showModal = false; check = true; schedule_deny_weeks = []; schedule_deny_days = ''; valueInModal = ''; scheduleInit();">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- 모달 내용 -->
          <div class="w-128 h-8 flex justify-end items-center">
            <label class="flex flex-row items-center px-4">
              <span class="mr-2 text-lg font-bold" x-text="'이번 달만 적용'"></span>
              <input type="checkbox" x-model="check">
            </label>
          </div>
          <div class="h-24 flex flex-col mt-4" :class="{'hidden': mb_type !== 'partner'}">
            <div class="basis-8 flex items-center font-bold text-xl">설치파트너 지정</div>
            <div class="pt-4 flex-1 flex flex-row">
              <div @click.away="closeListInModalbox()" @keydown.escape="closeListInModalbox()" class="relative w-full">
                <span class="inline-block w-full rounded-md shadow-sm">
                  <button x-ref="button" @click="toggleListboxInModalVisibility()" :aria-expanded="openInModal"
                    aria-haspopup="listbox2"
                    class="relative z-0 w-full py-2 pl-3 pr-10 text-left transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md cursor-default focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5">
                    <span id="select_manager" x-show="!openInModal"
                      x-text="valueInModal in optionsInModal ? optionsInModal[valueInModal] : placeholder"
                      :class="{ 'text-gray-500': ! (valueInModal in optionsInModal) }" class="block truncate"></span>

                    <input x-ref="searchInModal" x-show="openInModal" x-model="searchInModal"
                      @keydown.enter.stop.prevent="selectOptionInModal()"
                      @keydown.arrow-up.prevent="focusPreviousOptionInModal()"
                      @keydown.arrow-down.prevent="focusNextOptionInModal()" type="search"
                      class="w-full h-full p-0 form-control focus:outline-none" />

                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                      <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                        <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round"
                          stroke-linejoin="round"></path>
                      </svg>
                    </span>
                  </button>
                </span>

                <div x-show="openInModal" x-transition:leave="transition ease-in duration-100"
                  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                  class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg">
                  <ul x-ref="listbox2" @keydown.enter.stop.prevent="selectOptionInModal()"
                    @keydown.arrow-up.prevent="focusPreviousOptionInModal()"
                    @keydown.arrow-down.prevent="focusNextOptionInModal()" role="listbox2"
                    :aria-activedescendant="focusedOptionInModalIndex ? name + 'Option' + focusedOptionInModalIndex : null"
                    tabindex="-1"
                    class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                    <template x-for="(key, index) in Object.keys(optionsInModal)" :key="index">
                      <li :id="name + 'Option' + key + focusedOptionInModalIndex" @click="selectOptionInModal()"
                        @mouseenter="focusedOptionInModalIndex = index" @mouseleave="focusedOptionInModalIndex = null"
                        role="option" :aria-selected="focusedOptionInModalIndex === index"
                        :class="{ 'text-white bg-blue-600': index === focusedOptionInModalIndex, 'text-gray-900': index !== focusedOptionInModalIndex }"
                        class="relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9">
                        <span x-text="Object.values(optionsInModal)[index]"
                          :class="{ 'font-semibold': index === focusedOptionInModalIndex, 'font-normal': index !== focusedOptionInModalIndex }"
                          class="block font-normal truncate"></span>

                        <span x-show="key === valueInModal"
                          :class="{ 'text-white': index === focusedOptionInModalIndex, 'text-blue-600': index !== focusedOptionInModalIndex }"
                          class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd" />
                          </svg>
                        </span>
                      </li>
                    </template>

                    <div x-show="! Object.keys(optionsInModal).length" x-text="emptyOptionsMessage"
                      class="px-3 py-2 text-gray-900 cursor-default select-none"></div>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="h-32 flex flex-col mt-4">
            <div class="basis-8 flex items-center font-bold text-xl">
              설치(출고) 불가능 요일 설정
            </div>
            <div class="flex-1 border flex flex-col">
              <div class="flex-1 border-b flex flex-row">
                <template x-for="(day, index) in WEEKS" :key="index">
                  <div
                    class="flex-1 flex justify-center items-center text-lg font-bold bg-gray-100 border-r text-gray-600"
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
                x-model="schedule_deny_days" placeholder="설치(출고) 불가능한 날짜를 입력해주세요.(예시: 10,14,21)">
            </div>
          </div>
          <div class="h-16 flex justify-center items-center">
            <button
              class="border rounded-lg px-8 py-1 flex justify-center items-center text-lg bg-blue-500 text-white font-bold transition-colors duration-300"
              type="button"
              @click="if (valueInModal === '' || valueInModal === null) { alert('담당자를 선택해주세요.'); } else { req(calcDaysByMonth(), mb_type, valueInModal); }"
              x-text="'등록'"></button>
          </div>
        </div>
      </div>

      <!-- 일정 취소 모달 -->
      <div x-show="showCancelModal"
        class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-black bg-opacity-50"
        x-transition:enter="motion-safe:ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="motion-safe:ease-out duration-300"
        x-transition:leave-start="opacity-100 scale-90" x-transition:leave-end="opacity-0 scale-90">
        <div class="max-w-3xl px-6 py-4 mx-auto text-left bg-white rounded shadow-lg">
          <!-- 모달 상단 -->
          <div class="flex items-center justify-between">
            <h5 class="mr-3 font-bold text-xl max-w-none">일정 삭제</h5>
            <button type="button" class="z-50 cursor-pointer"
              @click="showCancelModal = false; selectPartnerMbId = ''; selectPartnerManageMbId = '';">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <!-- 모달 설명 -->
          <div class="flex items-center justify-between">
            일정을 정말 삭제하시겠습니까?
          </div>

          <!-- 모달 컨트롤바 -->
          <div class="h-16 flex justify-center items-center">
            <button
              class="border rounded-lg px-8 py-1 flex justify-center items-center text-lg bg-blue-500 text-white font-bold transition-colors duration-300"
              type="button"
              @click="removeDenySchedule(selectPartnerMbId, selectPartnerManageMbId, moment(select_date).format('YYYY-MM-DD'));"
              x-text="'삭제'"></button>
            <button
              class="border rounded-lg px-8 py-1 flex justify-center items-center text-lg bg-gray-300 text-white font-bold transition-colors duration-300"
              type="button" @click="showCancelModal = false; selectPartnerMbId = ''; selectPartnerManageMbId = '';"
              x-text="'취소'"></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  let target = document.getElementById("root");
  // 변경을 감지했을 때 실행할 부분
  let observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (!mutation.target.className.endsWith("modal-open")) {
        if ($(".popup_box")) {
          $(".popup_box").css("opacity", 0);
          $(".popup_box").css("display", 'none');
        }
      }
    });
  });
  // 감지 설정
  let config = {
    childList: true, // 타겟의 하위 요소 추가 및 제거 감지
    attributes: true, // 타켓의 속성 변경를 감지
    characterData: false, // 타겟의 데이터 변경 감지
    subtree: false, // 타겟의 자식 노드 아래로도 모두 감지
    attributeOldValue: false, // 타겟의 속성 변경 전 속성 기록
    characterDataOldValue: false // 타겟의 데이터 변경 전 데이터 기록
  };
  observer.observe(target, config);

  function goToUrl(od_id) {
    <?php
    if ($member["mb_type"] == "partner" || $member["mb_type"] == "manager") { // 설치파트너 & 매니저
      echo "$('body').addClass('modal-open');\n";
      echo "$('.popup_box > div').html('<iframe src=/shop/popup.partner_installreport.php?od_id=' + od_id + '\">');\n";
      echo "$('.popup_box iframe').load(function() {\n";
      echo "$('.popup_box').show();\n";
      echo "$('.popup_box').css('opacity', 1);\n";
      echo "$('.popup_box').css('display', 'table');\n";
      echo "});";
    } else {
      echo "let opt = 'width=1360,height=780,left=0,top=10';\n";
      echo "if (jQuery.browser.mobile) {\nopt = '';\n}\n";
      if ($member["mb_level"] >= 9) { // 관리자
        echo "const _url = '/adm/shop_admin/samhwa_orderform.php?od_id=' + od_id + '&sub_menu=400400';\n";
      } else { // 사업소
        echo "const _url = '/shop/orderinquiryview.php?od_id=' + od_id;\n";
      }
      echo "window.open(_url, 'win_schedule', opt);\n";
    }
    ?>
  }

  window.touchtime = 0;

  function global() {
    return {
      showModal: false,
      showCancelModal: false,
      selectPartnerMbId: '',
      selectPartnerManageMbId: '',
      doubleClick: function(e) {
        let target = null;
        if (e.target.id !== "") {
          target = e.target;
        } else {
          if (e.target.parentElement.id !== "") {
            target = e.target.parentElement;
          } else {
            if (e.target.parentElement.parentElement.id !== "") {
              target = e.target.parentElement.parentElement;
            } else {
              target = e.target.parentElement.parentElement.parentElement;
            }
          }
        }
        if ($(target).attr("data-partner-manager-mb-id") && $(target).attr(
            "data-partner-mb-id")) {
          this.selectPartnerMbId = $(target).attr("data-partner-mb-id");
          this.selectPartnerManageMbId = $(target).attr("data-partner-manager-mb-id");
          this.showCancelModal = true;
        }
      },
      removeDenySchedule: function(selectPartnerMbId, selectPartnerManageMbId, denyDate) {
        const data = {
          partner_mb_id: selectPartnerMbId,
          partner_manager_mb_id: selectPartnerManageMbId,
          deny_date: denyDate
        }
        let checkSum = true;
        $.ajax('ajax.delete_deny_schedule.php', {
          type: 'POST',
          cache: false,
          async: false,
          data,
          dataType: 'json',
          success: function(result) {
            // TODO: 새로고침이 아닌 방식으로 수정할 계획
            window.location.reload();
          },
          error: function($xhr) {
            checkSum = false;
            alert("서버 통신 에러");
          }
        });
        if (checkSum) {
          this.selectPartnerMbId = '';
          this.selectPartnerManageMbId = '';
          this.showCancelModal = false;
        }
      }
    }
  }
  </script>

  <script>
  function req(list, mb_type, valueInModal) {
    const data = {
      partner_mb_id: '<?php echo $_SESSION['ss_mb_id']; ?>',
      partner_manager_mb_id: mb_type === 'partner' ? valueInModal : '<?php echo $_SESSION['ss_manager_mb_id']; ?>',
      schedules: JSON.parse(JSON.stringify([...new Set(list.filter(e => moment().diff(moment(e), 'days') <=
        0))])),
    };
    let showModal = true;
    if (mb_type === 'partner' && valueInModal !== '') {
      $.ajax('ajax.deny_schedule.php', {
        type: 'POST',
        cache: false,
        async: false,
        data,
        dataType: 'json',
        success: function(result) {
          // TODO: 새로고침이 아닌 방식으로 수정할 계획
          window.location.reload();
        },
        error: function($xhr) {
          showModal = true;
          alert("설치 가능한 요일이 없습니다.");
        }
      });
    }
    return showModal;
  }
  </script>

  <script>
  function select(config) {
    let res;
    let mb_type;
    let resInModal;
    $.ajax('ajax.members.php', {
      type: 'POST',
      cache: false,
      async: false,
      data: {
        partner_mb_id: '<?php if ($_SESSION['ss_manager_mb_id']) echo $_SESSION['ss_manager_mb_id']; else echo $_SESSION['ss_mb_id']; ?>'
      },
      dataType: 'json',
      success: function(result) {
        res = result.data.members;
        mb_type = result.data.mb_type;
        resInModal = Object.fromEntries(Object.entries(result.data.members).filter((i) => i[0] !==
          'all'));
      },
      error: function($xhr) {
        alert("서버 통신 에러");
      }
    });
    return {
      data: res,
      dataInModal: resInModal,
      emptyOptionsMessage: config.emptyOptionsMessage ?? '검색한 담당자가 존재하지 않습니다.',
      focusedOptionIndex: null,
      focusedOptionInModalIndex: null,
      name: config.name,
      open: false,
      openInModal: false,
      options: {},
      optionsInModal: {},
      placeholder: config.placeholder ?? '담당자 선택',
      search: '',
      searchInModal: '',
      value: config.value,
      valueInModal: config.valueInModal,
      filter_mb_id: '',
      mb_type,
      closeListbox: function() {
        this.open = false;
        this.focusedOptionIndex = null;
        this.search = '';
      },
      closeListInModalbox: function() {
        this.openInModal = false;
        this.focusedOptionInModalIndex = null;
        this.searchInModal = '';
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
      focusNextOptionInModal: function() {
        if (this.focusedOptionInModalIndex === null) return this.focusedOptionInModalIndex = Object.keys(
            this
            .optionsInModal).filter((i) => i !==
            'all')
          .length - 1
        if (this.focusedOptionInModalIndex + 1 >= Object.keys(this.optionsInModal).filter((i) => i !==
            'all').length) return
        this.focusedOptionInModalIndex++
        this.$refs.listbox2.children[this.focusedOptionInModalIndex].scrollIntoView({
          block: "center",
        })
      },
      focusPreviousOptionInModal: function() {
        if (this.focusedOptionInModalIndex === null) return this.focusedOptionInModalIndex = 0
        if (this.focusedOptionInModalIndex <= 0) return
        this.focusedOptionInModalIndex--
        this.$refs.listbox2.children[this.focusedOptionInModalIndex].scrollIntoView({
          block: "center",
        })
      },
      init: function() {
        this.options = this.data;
        this.optionsInModal = this.dataInModal;
        if (!(this.value in this.options)) this.value = null
        if (!(this.valueInModal in this.optionsInModal)) this.valueInModal = null
        this.$watch('search', ((value) => {
          if (!this.open || !value) return this.options = this.data;
          this.options = Object.keys(this.data)
            .filter((key) => this.data[key].toLowerCase().includes(value
              .toLowerCase()))
            .reduce((options, key) => {
              options[key] = this.data[key]
              return options
            }, {})
        }));
        this.$watch('searchInModal', ((value) => {
          if (!this.openInModal || !value) return this.optionsInModal = this.dataInModal;
          this.optionsInModal = Object.keys(this.dataInModal)
            .filter((key) => this.data[key].toLowerCase().includes(value
              .toLowerCase()))
            .reduce((options, key) => {
              optionsInModal[key] = this.data[key]
              return optionsInModal
            }, {})
        }));
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
      selectOptionInModal: function() {
        if (!this.openInModal) return this.toggleListboxInModalVisibility()
        this.filter_mb_id = Object.keys(this.optionsInModal)[this
            .focusedOptionInModalIndex] == 'all' ? '' :
          Object
          .keys(
            this.optionsInModal)[this.focusedOptionInModalIndex];
        this.valueInModal = Object.keys(this.optionsInModal)[this
          .focusedOptionInModalIndex]
        this.closeListInModalbox()
      },
      toggleListboxInModalVisibility: function() {
        if (this.openInModal) return this.closeListInModalbox()
        this.focusedOptionInModalIndex = Object.keys(this.optionsInModal)
          .indexOf(this.valueInModal)
        if (this.focusedOptionInModalIndex < 0) this.focusedOptionInModalIndex = 0
        this.openInModal = true
        this.$nextTick(() => {
          this.$refs.searchInModal.focus()
          this.$refs.listbox2.children[this.focusedOptionInModalIndex].scrollIntoView({
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
        partner_mb_id: '<?php if ($_SESSION['ss_manager_mb_id']) echo $_SESSION['ss_manager_mb_id']; else echo $_SESSION['ss_mb_id']; ?>'
      },
      dataType: 'json',
      success: (result) => {
        res = result.data;
      },
      error: ($xhr) => {
        let message = $xhr.responseJSON.message;
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
      // mb_type: '<?php echo $member["mb_type"]; ?>',
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
        for (let i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
        let daysArray = [];
        for (let i = 1; i <= daysInMonth; i++) daysArray.push(i);
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
        for (let i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
        let daysArray = [];
        for (let i = 1; i <= daysInMonth; i++) daysArray.push(i);
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
        for (let i = 1; i <= dayOfWeek; i++) blankDaysArray.push(i);
        let daysArray = [];
        for (let i = 1; i <= daysInMonth; i++) daysArray.push(i);
        this.month = month;
        this.year = year;
        this.blankDays = blankDaysArray;
        this.no_of_days = daysArray;
      },
    };
  }
  </script>

  <script>
  // 1 : 월, 2 : 화, 3 : 수, 4 : 목, 5 : 금, 6 : 토, 0 : 일
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
        this.check = true;
      },
      refetch: function() {
        $.ajax('ajax.index.php', {
          type: 'POST',
          cache: false,
          async: false,
          data: {
            partner_mb_id: '<?php echo $_SESSION['ss_manager_mb_id']; ?>'
          },
          dataType: 'json',
          success: (result) => {
            this.events = result.data;
            this.schedule_deny_weeks = [];
            this.schedule_deny_days = ""; // 사용자가 직접 입력한 설치 불가능 요일
            this.check = true;
          },
          error: ($xhr) => {
            let message = $xhr.responseJSON.message;
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
      }
    }
  }
  </script>
</div>