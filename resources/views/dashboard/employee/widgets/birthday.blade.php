@if (in_array('birthday', $activeWidgets))
    <!-- EMP DASHBOARD BIRTHDAY START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-3" :title="__('modules.dashboard.birthday')" padding="false" otherClasses="h-200">
            <x-table>
                @forelse ($upcomingBirthdays as $upcomingBirthday)
                    <tr>
                        <td class="pl-20">
                            <x-employee :user="$upcomingBirthday->user" />
                        </td>
                        <td>
                                                <span class="badge badge-secondary p-2">
                                                    <i class="fa fa-birthday-cake"></i>
                                                    {{ $upcomingBirthday->date_of_birth->format('d') }}
                                                    {{ $upcomingBirthday->date_of_birth->format('M') }}
                                                </span>
                        </td>
                        <td class="pr-20" align="right">
                            @php
                                $currentYear = now()->year;
                                $dateBirth = $upcomingBirthday->date_of_birth->format($currentYear . '-m-d');
                                $dateBirth = \Carbon\Carbon::parse($dateBirth);

                                $currentDay = \Carbon\Carbon::parse(now(company()->timezone)->toDateTimeString())->startOfDay()->setTimezone('UTC');
                                $diffInDays = $dateBirth->copy()->diffForHumans($currentDay);
                            @endphp

                            @if ($dateBirth->setTimezone(company()->timezone)->isToday())
                                <span class="badge badge-light p-2">@lang('app.today')</span>
                            @else
                                <span class="badge badge-light p-2">{{ $diffInDays }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="shadow-none">
                            <x-cards.no-record icon="birthday-cake" :message="__('messages.noRecordFound')" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
    <!-- EMP DASHBOARD BIRTHDAY END -->
@endif
