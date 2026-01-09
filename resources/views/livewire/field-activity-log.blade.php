<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route(config('custom-fields.route.name_prefix') . 'edit', $field) }}"
               class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Back to Edit Field
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Activity Log</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Change history for: <span class="font-semibold">{{ $field->name }}</span> ({{ $field->code }})
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filters</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Event Type
                </label>
                <select wire:model.live="filterEvent"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">All Events</option>
                    @foreach($eventTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    User
                </label>
                <select wire:model.live="filterUserId"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name ?? $user->email }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button wire:click="resetFilters"
                        class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Activity Log Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        @if($activityLogs->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No activity found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($filterEvent || $filterUserId)
                        Try adjusting your filters.
                    @else
                        Activity will appear here once changes are made to this field.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($activityLogs as $log)
                    <div class="relative pl-8 pb-6 border-l-2 border-gray-200 dark:border-gray-700 last:pb-0">
                        <!-- Timeline dot -->
                        <div class="absolute -left-2 top-0 w-4 h-4 rounded-full {{ $log->getEventColorClass() }} border-2 border-white dark:border-gray-800"></div>

                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->getEventColorClass() }}">
                                        {{ $log->getEventLabel() }}
                                    </span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $log->created_at->format('M d, Y \a\t H:i:s') }}
                                    </span>
                                </div>
                                @if($log->user)
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        by <span class="font-medium text-gray-900 dark:text-white">{{ $log->user->name ?? $log->user->email }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($log->description)
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    {{ $log->description }}
                                </p>
                            @endif

                            <!-- Changes -->
                            @if(!empty($log->changed_attributes) && ($log->old_values || $log->new_values))
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Changes</h4>
                                    <div class="space-y-2">
                                        @foreach($log->changed_attributes as $attribute)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                                <div>
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        {{ ucfirst(str_replace('_', ' ', $attribute)) }}
                                                    </span>
                                                    <div class="mt-1">
                                                        <span class="inline-block px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-xs">
                                                            Old:
                                                            @if(isset($log->old_values[$attribute]))
                                                                @if(is_array($log->old_values[$attribute]))
                                                                    <code>{{ json_encode($log->old_values[$attribute]) }}</code>
                                                                @elseif($log->old_values[$attribute] === null)
                                                                    <span class="italic">null</span>
                                                                @elseif($log->old_values[$attribute] === '')
                                                                    <span class="italic">empty</span>
                                                                @else
                                                                    {{ $log->old_values[$attribute] }}
                                                                @endif
                                                            @else
                                                                <span class="italic">null</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        &nbsp;
                                                    </span>
                                                    <div class="mt-1">
                                                        <span class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs">
                                                            New:
                                                            @if(isset($log->new_values[$attribute]))
                                                                @if(is_array($log->new_values[$attribute]))
                                                                    <code>{{ json_encode($log->new_values[$attribute]) }}</code>
                                                                @elseif($log->new_values[$attribute] === null)
                                                                    <span class="italic">null</span>
                                                                @elseif($log->new_values[$attribute] === '')
                                                                    <span class="italic">empty</span>
                                                                @else
                                                                    {{ $log->new_values[$attribute] }}
                                                                @endif
                                                            @else
                                                                <span class="italic">null</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Metadata -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($log->ip_address)
                                        <div>
                                            <span class="font-medium">IP:</span> {{ $log->ip_address }}
                                        </div>
                                    @endif
                                    @if($log->user_agent)
                                        <div class="md:col-span-2">
                                            <span class="font-medium">User Agent:</span>
                                            <span class="break-all">{{ $log->user_agent }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $activityLogs->links() }}
            </div>
        @endif
    </div>
</div>
