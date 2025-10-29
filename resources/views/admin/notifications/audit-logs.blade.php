@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Nhật ký Kiểm toán Thông báo</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hành động</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Tạo</option>
                    <option value="read" {{ request('action') === 'read' ? 'selected' : '' }}>Đọc</option>
                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Xóa</option>
                    <option value="sent_email" {{ request('action') === 'sent_email' ? 'selected' : '' }}>Gửi Email</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Thành công</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.notifications.audit-logs') }}" class="btn btn-secondary">Xóa</a>
                <a href="{{ route('admin.notifications.audit-logs.export', request()->query()) }}" class="btn btn-secondary">Xuất CSV</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Người dùng</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành động</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Thời gian</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $log->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $log->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $log->action === 'created' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $log->action === 'read' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->action === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $log->action === 'sent_email' ? 'bg-purple-100 text-purple-800' : '' }}
                            ">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                            ">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.notifications.audit-logs.show', $log) }}" class="text-blue-600 hover:text-blue-900">Chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
</div>
@endsection
