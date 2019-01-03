<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Timerecord;

class ValidTime implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    private $from;
    private $timerecord;
    private $id;

    public function __construct($from, $timerecord, $id = 0)
    {
        $this->from = new \DateTime($from);
        $this->timerecord = $timerecord;
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $to)
    {
        $to = new \DateTime($to);
        foreach ($this->timerecord->hours as $hour) {
            if ($this->id != $hour->id) {
                if ($this->from >= $hour->from() && $this->from < $hour->to()) {
                    return false;
                }
                if ($to > $hour->from() && $to < $hour->to()) {
                    return false;
                }
                if ($this->from <= $hour->from() && $to >= $hour->to()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Die Zeit überschneidet sich mit einem anderen Eintrag.';
    }
}
