@extends('layouts.admin')

@section('title', 'Categories - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Product Categories</h1>
@endsection

@section('content')
    <div class="space-y-6 pb-10" x-data="categoryCrud()">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">            

            @if (session('success'))
                <div
                    class="bg-[#dcfce7] text-[#16a34a] px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm shadow-sm flex flex-col gap-2 w-full sm:w-auto">
                    <div class="flex items-center gap-2 font-bold">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside font-medium text-xs text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div
                class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-[1.15rem] font-bold text-[#212538] tracking-tight">All Categories</h2>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-64 relative">
                        <input type="text" x-model="search" placeholder="Search categories..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all placeholder:text-gray-400">
                    </div>

                    @if(has_permission('categories.create'))
                    <button @click="openCreateModal()"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-1.5 transition-colors shadow-sm whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add
                    </button>
                    @endif

                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="hidden md:table-header-group text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr>
                            <th class="px-6 py-4 w-1/3">CATEGORY NAME</th>
                            <th class="px-6 py-4 w-1/3">IMAGE</th>
                            <th class="px-6 py-4 w-1/4 text-center">STATUS</th>
                            <th class="px-6 py-4 w-1/3 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($categories as $category)
                            <tr class="flex flex-col md:table-row border-b md:border-b-0 border-gray-100 p-4 md:p-0 hover:bg-gray-50/50 transition-colors relative"
                                x-show="matchesSearch('{{ strtolower($category->name) }}')">
                                <td class="block md:table-cell px-0 py-2 md:px-6 md:py-4 w-full md:w-auto">
                                    <div class="md:hidden text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Category Name</div>
                                    <span class="font-bold text-[#475569] text-[15px] md:text-[13.5px] pr-20">{{ $category->name }}</span>
                                </td>

                                <td class="block md:table-cell px-0 py-2 md:px-6 md:py-4 w-full md:w-auto">
                                    <div class="md:hidden text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Image</div>
                                    <div class="w-[50px] h-[50px] md:w-[45px] md:h-[45px] rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center shadow-sm">
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                    </div>
                                </td>

                                <td class="block flex justify-between items-center md:table-cell px-0 py-2 md:px-6 md:py-4 w-full md:w-auto md:text-center">
                                        <div class="md:hidden text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</div>
                                    @if ($category->is_active)
                                        <span
                                            class="bg-[#dcfce7] text-[#16a34a] px-3 py-1 rounded-md font-bold text-[11px] uppercase tracking-wider">Active</span>
                                    @else
                                        <span
                                            class="bg-gray-200 text-gray-500 px-3 py-1 rounded-md font-bold text-[11px] uppercase tracking-wider">Inactive</span>
                                    @endif
                                </td>

                                <td class="absolute top-4 right-4 md:relative md:top-auto md:right-auto block md:table-cell px-0 py-0 md:px-6 md:py-4 w-auto">
                                    <div class="flex items-center justify-end gap-2.5">
                                        @if(has_permission('categories.update'))
                                        <button
                                            @click="openEditModal({{ $category->toJson() }}, '{{ $category->image_url }}')"
                                            class="w-[32px] h-[32px] flex items-center justify-center rounded border border-[#108c2a] text-[#108c2a] hover:bg-green-50 transition-colors"
                                            title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>
                                        @endif

                                        @if(has_permission('categories.delete'))
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                            @submit.prevent="confirmDelete($event.target)" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-[32px] h-[32px] flex items-center justify-center rounded border border-red-400 text-red-500 hover:bg-red-50 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 font-medium">No categories
                                    found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="h-12 border-t border-gray-100 bg-white w-full"></div>
        </div>

        <div x-show="isModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-md p-4" @click.away="closeModal()">
                <div class="relative bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">

                    <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-white">
                        <h3 class="text-lg font-bold text-[#212538]"
                            x-text="modalMode === 'create' ? 'Add New Category' : 'Edit Category'"></h3>
                        <button @click="closeModal()" type="button"
                            class="text-gray-400 hover:bg-gray-100 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" enctype="multipart/form-data" class="p-5"
                        @submit="BizAlert.loading('Uploading...')">
                        @csrf
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">
                            <div class="flex flex-col items-center justify-center mb-2">
                                <div
                                    class="w-24 h-24 rounded-xl border-2 border-dashed border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center relative group">
                                    <template x-if="imagePreview">
                                        <img :src="imagePreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!imagePreview">
                                        <i data-lucide="image" class="w-8 h-8 text-gray-300"></i>
                                    </template>
                                    <label
                                        class="absolute inset-0 bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity text-[10px] font-bold">
                                        CHANGE
                                        <input type="file" name="image_file" class="hidden"
                                            @change="previewFile($event)" accept="image/*">
                                    </label>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-2 font-bold uppercase tracking-wider">Category
                                    Thumbnail</span>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="formData.name" required
                                    placeholder="e.g. Indoor Plants"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all">
                            </div>

                            <div class="mt-4">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Slug (Optional)</label>
                                <input type="text" name="slug" x-model="formData.slug"
                                    placeholder="Leave blank to auto-generate"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all">
                                <p class="text-[10px] text-gray-400 mt-1 font-medium">Determines the URL (e.g., /category/indoor-plants)</p>
                            </div>

                            <div class="flex items-center pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]">
                                    </div>
                                    <span class="ms-3 text-sm font-bold text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full mt-6 text-white bg-[#108c2a] hover:bg-[#0c6b1f] font-bold rounded-lg text-sm px-5 py-3 transition-colors shadow-sm">
                            <span x-text="modalMode === 'create' ? 'Save Category' : 'Update Category'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function categoryCrud() {
            return {
                search: '',
                isModalOpen: false,
                modalMode: 'create',
                formAction: '{{ route('admin.categories.store') }}',
                imagePreview: null,
                formData: {
                    name: '',
                    slug: '',
                    is_active: true
                },

                matchesSearch(name) {
                    return this.search === '' || name.includes(this.search.toLowerCase());
                },

                previewFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.imagePreview = URL.createObjectURL(file);
                    }
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.formAction = '{{ route('admin.categories.store') }}';
                    this.formData = {
                        name: '',
                        slug: '',
                        is_active: true
                    };
                    this.imagePreview = null;
                    this.isModalOpen = true;
                },

                openEditModal(cat, imgUrl) {
                    this.modalMode = 'edit';
                    this.formAction = `/admin/categories/${cat.id}`;
                    this.formData = {
                        name: cat.name,
                        slug: cat.slug || '',
                        is_active: cat.is_active
                    };
                    this.imagePreview = imgUrl;
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                confirmDelete(form) {
                    BizAlert.confirm('Delete Category?', 'This will hide the category and its associated data.',
                            'Yes, Delete')
                        .then((result) => {
                            if (result.isConfirmed) {
                                BizAlert.loading('Removing...');
                                form.submit();
                            }
                        });
                }
            }
        }
    </script>
@endpush
