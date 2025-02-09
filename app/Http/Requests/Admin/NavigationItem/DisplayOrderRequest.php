<?php

namespace App\Http\Requests\Admin\NavigationItem;

use App\Models\NavigationItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DisplayOrderRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->display_order) {
                    $validator->errors()->add(
                        'display_order',
                        'The display order field is required.'
                    );
                } elseif (! is_array($this->display_order)) {
                    $validator->errors()->add(
                        'display_order',
                        'The display order field must be an array.'
                    );
                } elseif (! count($this->display_order)) {
                    $validator->errors()->add(
                        'display_order',
                        'The display order field must be an array.'
                    );
                } else {
                    $itemIDs = NavigationItem::get('id');
                    $size = $itemIDs->count();
                    $itemIDs = $itemIDs->pluck('id')->toArray();
                    $masterIDs = $itemIDs;
                    $masterIDs[] = '0';
                    $IDs = [];
                    foreach ($this->display_order as $masterID => $array) {
                        if (! is_array($array)) {
                            $validator->errors()->add(
                                "display_order.$masterID",
                                "The display_order.$masterID field must be an array."
                            );
                        } elseif (! count($array)) {
                            $validator->errors()->add(
                                "display_order.$masterID",
                                "The display_order.$masterID field is required."
                            );
                        } elseif (! preg_match('/^-?\d+$/', $masterID)) {
                            $validator->errors()->add(
                                'display_order',
                                'The array key of display_order field must be an integer.'
                            );
                        } elseif (! in_array($masterID, $masterIDs)) {
                            $validator->errors()->add(
                                'message',
                                'The master ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.'
                            );
                        } else {
                            foreach ($array as $order => $id) {
                                if (! preg_match('/^-?\d+$/', $id)) {
                                    $validator->errors()->add(
                                        "display_order.$masterID.$order",
                                        "The display_order.$masterID.$order field must be an integer."
                                    );
                                } elseif (! in_array($id, $itemIDs)) {
                                    $validator->errors()->add(
                                        'message',
                                        'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.'
                                    );
                                }
                                $IDs[] = $id;
                            }
                        }
                    }
                    $countIDs = count($IDs);
                    if ($countIDs != $size) {
                        $validator->errors()->add(
                            'message',
                            'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.'
                        );
                    } elseif (count(array_unique($IDs)) != $countIDs) {
                        $validator->errors()->add(
                            'message',
                            'The ID(s) of display order field has a duplicate value. If the problem persists, please contact I.T. officer.'
                        );
                    }
                }
            },
        ];
    }
}
