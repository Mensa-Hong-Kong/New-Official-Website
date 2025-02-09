<?php

namespace App\View\Components;

use App\Models\NavigationItem;
use Illuminate\View\Component;
use Illuminate\View\View;

class NavbarRoot extends Component
{
    public array $items = [];

    private function children($items, $masterID)
    {
        $return = [];
        foreach ($items as $id => $thisItem) {
            if ($thisItem['master_id'] == $masterID) {
                $item = [
                    'name' => $thisItem['name'],
                    'url' => $thisItem['url'],
                ];
                unset($items[$id]);
                $children = $this->children($items, $id);
                $items = array_diff_key($items, $children);
                if (count($children)) {
                    $item['children'] = $children;
                }
                $return[$id] = $item;
            }
        }

        return $return;
    }

    public function __construct()
    {
        $this->items = $this->children(
            NavigationItem::orderBy('display_order')
                ->get(['id', 'master_id', 'name', 'url'])
                ->keyBy('id')
                ->toArray(),
            null
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.navbar-root');
    }
}
