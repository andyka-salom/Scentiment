@php
    $form = $form ?? null;
    $tabs = [
        ['name' => 'Builder',     'route' => 'forms.build',      'icon' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z'],
        ['name' => 'Pengaturan',  'route' => 'forms.settings',   'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['name' => 'Preview',     'route' => 'forms.preview',    'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['name' => 'Bagikan',     'route' => 'forms.share',      'icon' => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z'],
        ['name' => 'Respons',     'route' => 'forms.responses',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['name' => 'Analitik',    'route' => 'forms.analytics',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];
    $statusColors = [
        'draft'     => 'bg-slate-100 text-slate-600',
        'published' => 'bg-emerald-100 text-emerald-700',
        'closed'    => 'bg-rose-100 text-rose-700',
        'archived'  => 'bg-amber-100 text-amber-700',
    ];
    $statusLabels = ['draft' => 'Draft', 'published' => 'Publik', 'closed' => 'Ditutup', 'archived' => 'Arsip'];
@endphp

@if($form)
<!-- Form Context Header -->
<div class="mb-0 -mt-4 sm:-mt-6 lg:-mt-8 -mx-4 sm:-mx-6 lg:-mx-8 mb-6">
    <!-- Breadcrumb & Header Bar -->
    <div class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 pt-4 pb-0">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-3">
            <a href="{{ route('forms.index') }}" class="hover:text-slate-700 transition-colors flex items-center gap-1">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Form Saya
            </a>
            <span>/</span>
            <span class="text-slate-700 font-medium truncate max-w-[200px]">{{ $form->title }}</span>
        </nav>

        <!-- Title row -->
        <div class="flex items-center gap-3 mb-4">
            <h1 class="font-outfit font-bold text-xl text-slate-900 truncate flex-1">{{ $form->title }}</h1>

            <!-- Status Badge -->
            <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$form->status] ?? 'bg-slate-100 text-slate-600' }}">
                {{ $statusLabels[$form->status] ?? $form->status }}
            </span>

            <!-- Publish / Unpublish Button -->
            @can('update', $form)
                @if($form->status === 'draft')
                    <form method="POST" action="{{ route('forms.status.update', $form) }}" class="shrink-0">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="published">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Publish
                        </button>
                    </form>
                @elseif($form->status === 'published')
                    <form method="POST" action="{{ route('forms.status.update', $form) }}" class="shrink-0">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="draft">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                            Unpublish
                        </button>
                    </form>
                @endif
            @endcan
        </div>

        <!-- Tabs -->
        <div class="flex gap-0 -mb-px overflow-x-auto">
            @foreach($tabs as $tab)
                @php
                    $isActive = request()->routeIs($tab['route']);
                    $canAccess = true;
                    if (in_array($tab['route'], ['forms.build', 'forms.settings', 'forms.share'])) {
                        $canAccess = auth()->user()->can('update', $form);
                    }
                @endphp
                @if($canAccess)
                    <a href="{{ route($tab['route'], $form) }}"
                       class="flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                              {{ $isActive
                                  ? 'border-slate-900 text-slate-900'
                                  : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $tab['icon'] }}" />
                        </svg>
                        {{ $tab['name'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
