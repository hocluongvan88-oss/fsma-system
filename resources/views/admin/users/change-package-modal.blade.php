@props(['user'])

<div x-data="{ 
    showModal: false, 
    selectedPackage: '{{ $user->package_id }}',
    packages: @js(\App\Models\Package::visible()->ordered()->get()->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'description' => $p->description,
        'max_cte_records_monthly' => $p->max_cte_records_monthly,
        'max_documents' => $p->max_documents,
        'max_users' => $p->max_users,
        'features' => $p->features ?? []
    ]))
}">
    <button @click="showModal = true" class="btn btn-primary btn-sm">
        {{ __('messages.change_package') }}
    </button>

    <div x-show="showModal"
         x-cloak
         @click.away="showModal = false"
         style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem;">
        <div @click.stop 
             style="background: var(--bg-primary); border-radius: 1rem; padding: 2rem; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                {{ __('messages.change_package_for_user', ['name' => $user->full_name]) }}
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                {{ __('messages.select_new_package_description') }}
            </p>

            <form method="POST" action="{{ route('admin.users.update-package', $user) }}">
                @csrf
                {{-- Changed from @method('PUT') to match POST route definition --}}

                <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
                    <template x-for="pkg in packages" :key="pkg.id">
                        <div @click="selectedPackage = pkg.id"
                             :class="selectedPackage === pkg.id ? 'border: 2px solid var(--accent-primary); background: var(--bg-tertiary);' : 'border: 1px solid var(--border);'"
                             style="padding: 1rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;">
                            <div style="display: flex; align-items: start; gap: 1rem;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--accent-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.25rem;">
                                    <div x-show="selectedPackage === pkg.id" 
                                         style="width: 12px; height: 12px; border-radius: 50%; background: var(--accent-primary);"></div>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;" x-text="pkg.name"></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.75rem;" x-text="pkg.description"></div>
                                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.75rem;">
                                        <div>
                                            <span style="color: var(--text-secondary);">{{ __('messages.cte_records') }}:</span>
                                            <span style="font-weight: 600;" x-text="pkg.max_cte_records_monthly === 0 ? '{{ __('messages.unlimited') }}' : pkg.max_cte_records_monthly.toLocaleString()"></span>
                                        </div>
                                        <div>
                                            <span style="color: var(--text-secondary);">{{ __('messages.documents') }}:</span>
                                            <span style="font-weight: 600;" x-text="pkg.max_documents === 0 ? '{{ __('messages.unlimited') }}' : pkg.max_documents.toLocaleString()"></span>
                                        </div>
                                        <div>
                                            <span style="color: var(--text-secondary);">{{ __('messages.users') }}:</span>
                                            <span style="font-weight: 600;" x-text="pkg.max_users === 0 ? '{{ __('messages.unlimited') }}' : pkg.max_users"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <input type="hidden" name="package_id" x-model="selectedPackage">

                <div style="background: var(--warning-bg); border-left: 4px solid var(--warning); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem; color: var(--warning);">
                        ⚠️ {{ __('messages.important') }}
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ __('messages.package_change_warning') }}
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" @click="showModal = false" class="btn btn-secondary" style="flex: 1;">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        {{ __('messages.confirm_change') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush
