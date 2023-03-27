<?php

namespace App\Observers;

use App\Events\NewExpenseRecurringEvent;
use App\Models\ExpenseRecurring;
use App\Models\Notification;

class ExpenseRecurringObserver
{

    public function saving(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->last_updated_by = user()->id;
        }
    }

    public function creating(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $expense->added_by = user()->id;
        }

        if (company()) {
            $expense->company_id = company()->id;
        }
    }

    public function created(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new NewExpenseRecurringEvent($expense, ''));
        }
    }

    public function updated(ExpenseRecurring $expense)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($expense->isDirty('status')) {
                event(new NewExpenseRecurringEvent($expense, 'status'));
            }
        }
    }

    public function deleting(ExpenseRecurring $expense)
    {
        $notifyData = ['App\Notifications\NewExpenseRecurringMember', 'App\Notifications\ExpenseRecurringStatus'];
        \App\Models\Notification::deleteNotification($notifyData, $expense->id);

    }

}
