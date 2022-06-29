<?php

namespace App\Jobs;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    private string $chatId;

    /** @var int */
    private int $userId;

    /**
     * @param string $chatId
     * @param int $userId
     */
    public function __construct(string $chatId, int $userId)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            ($query = ChatMessage::query())
                ->where($query->qualifyColumn('chat_id'), $this->chatId)
                ->whereDoesntHave(
                    'userDeleteMessages',
                    function (Builder $query) {
                        $query->where('user_id', $this->userId);
                    }
                )->chunk(100, function (Collection $chatMessages) {
                    $chatMessages->map(
                        fn($chatMessage) => $chatMessage->userDeleteMessages()->attach(
                            [
                                $this->userId
                            ]
                        )
                    );
                });
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::debug($e->getMessage());
        }
    }
}
