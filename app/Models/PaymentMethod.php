<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use App\Models\Navigation;

    class PaymentMethod extends Model {
        protected $fillable = [
            "name",
        ];
    }
?>