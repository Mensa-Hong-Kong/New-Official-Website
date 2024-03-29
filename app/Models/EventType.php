<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use App\Models\Navigation;

    class EventType extends Model {
        protected $fillable = [
            "name",
        ];
        public function events() {
            return $this->hasMany( Event::class, "type_id" );
        }
    }
?>
