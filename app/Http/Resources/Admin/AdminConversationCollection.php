<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\User;
use App\Http\Resources\Admin\AdminConversationResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection as SupportCollection;

class AdminConversationCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     * Cần thiết để ResourceCollection cha biết loại resource con khi tạo response.
     * @var string
     */
    public $collects = AdminConversationResource::class;

    /**
     * The authenticated admin user instance.
     * @var \App\Models\User
     */
    protected User $adminUserContext;

    /**
     * The original paginator instance.
     * We need this to preserve pagination information.
     * @var \Illuminate\Pagination\AbstractPaginator
     */
    protected AbstractPaginator $originalPaginator;

    /**
     * Create a new resource instance.
     *
     * @param  \Illuminate\Pagination\AbstractPaginator  $paginator The paginator instance of Conversation models.
     * @param  \App\Models\User $adminUser The authenticated admin user.
     * @return void
     */
    public function __construct(AbstractPaginator $paginator, User $adminUser)
    {
        $this->adminUserContext = $adminUser;
        $this->originalPaginator = $paginator; // Lưu Paginator gốc

        Log::info('AdminConversationCollection CONSTRUCTOR: adminUserContext and originalPaginator set.', [
            'admin_id' => $this->adminUserContext->id,
            'original_paginator_total_items' => $this->originalPaginator->total()
        ]);

        // 1. Lấy các model gốc từ Paginator gốc
        $originalModels = $paginator->items(); // Đây là một mảng các Conversation model

        // 2. Biến đổi các model gốc thành AdminConversationResource instances, truyền context vào
        $correctlyMappedResources = collect($originalModels)->map(function ($conversationModel) {
            if (!$conversationModel instanceof \App\Models\Conversation) {
                Log::warning('AdminConversationCollection CONSTRUCTOR (map): Item is not a Conversation model.', [
                    'type' => is_object($conversationModel) ? get_class($conversationModel) : gettype($conversationModel)
                ]);
                return null; // Sẽ được filter sau nếu cần
            }
            // Đảm bảo type-hint trong AdminConversationResource constructor là đúng
            return new AdminConversationResource($conversationModel, $this->adminUserContext);
        })->filter()->values(); // filter để loại bỏ null, values để re-index

        Log::info('AdminConversationCollection CONSTRUCTOR: Created collection of AdminConversationResource.', [
            'count_mapped_resources' => $correctlyMappedResources->count()
        ]);

        // 3. Gọi parent constructor với collection CÁC RESOURCE ĐÃ ĐƯỢC BIẾN ĐỔI ĐÚNG CÁCH
        // $this->collects sẽ được sử dụng bởi lớp cha.
        // Vì các item đã là resource, lớp cha sẽ không cố gắng "new" lại chúng.
        parent::__construct($correctlyMappedResources);
    }

    /**
     * Get the underlying Lạc nguyên bản.
     * Override để đảm bảo các phương thức của ResourceCollection cha
     * (đặc biệt là preparePaginatedResponse) sử dụng Paginator gốc cho thông tin phân trang.
     *
     * @return \Illuminate\Pagination\AbstractPaginator
     */
    public function resource(): AbstractPaginator
    {
        return $this->originalPaginator;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this->collection bây giờ đã là một Illuminate\Support\Collection
        // chứa các AdminConversationResource đã được tạo đúng cách trong constructor.
        // parent::toArray() sẽ duyệt qua $this->collection và gọi toArray() trên mỗi item.
        Log::info('AdminConversationCollection toArray(): Delegating to parent::toArray().', [
            'collection_count' => $this->collection->count()
        ]);
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        // Phương thức with mặc định của ResourceCollection sẽ xử lý việc thêm thông tin
        // phân trang nếu resource là Paginator (thông qua phương thức resource() đã ghi đè).
        // Chúng ta chỉ cần thêm các dữ liệu tùy chỉnh của mình.
        return [
            'success' => true,
            'message' => 'Conversations retrieved successfully.',
            // Laravel sẽ tự động thêm 'links' và 'meta' vào response
            // nếu ResourceCollection được tạo với Paginator và phương thức toResponse được gọi.
        ];
    }

    // Không cần ghi đè collectResource() vì chúng ta đã tự xử lý collection trong constructor.
}