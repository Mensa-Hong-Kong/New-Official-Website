<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Library\Stripe\Models{
/**
 * @property string $id
 * @property string $customerable_type
 * @property int $customerable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $customerable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCustomerableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCustomerableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class StripeCustomer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $district_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $admissionTests
 * @property-read int|null $admission_tests_count
 * @property-read \App\Models\District|null $district
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $user
 * @property-read int|null $user_count
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereValue($value)
 * @mixin \Eloquent
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $type_id
 * @property \Illuminate\Support\Carbon $testing_at
 * @property \Illuminate\Support\Carbon|null $expect_end_at
 * @property int|null $location_id
 * @property int|null $address_id
 * @property int|null $maximum_candidates
 * @property bool $is_free
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\AdmissionTestHasProctor|\App\Models\AdmissionTestHasCandidate|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $candidates
 * @property-read int|null $candidates_count
 * @property-read mixed $current_user_is_proctor
 * @property-read mixed $in_testing_time_range
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $proctors
 * @property-read int|null $proctors_count
 * @property-read \App\Models\AdmissionTestType|null $type
 * @method static \Database\Factories\AdmissionTestFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereExpectEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereIsFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereMaximumCandidates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereTestingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class AdmissionTest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property int|null $order_id
 * @property int|null $seat_number
 * @property bool|null $is_present
 * @property bool|null $is_pass
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $candidate
 * @property-read mixed $has_result
 * @property-read mixed $is_free
 * @property-read \App\Models\AdmissionTest $test
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereIsPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereIsPresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereSeatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereUserId($value)
 * @mixin \Eloquent
 */
	class AdmissionTestHasCandidate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereUserId($value)
 * @mixin \Eloquent
 */
	class AdmissionTestHasProctor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property int $quota
 * @property string $status
 * @property \Illuminate\Support\Carbon $expired_at
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property int $returned_quota
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read mixed $has_unused_quota
 * @property-read \App\Models\AdmissionTest|null $lastTest
 * @property-read mixed $quota_expired_on
 * @property-read \App\Models\AdmissionTestHasCandidate|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $tests
 * @property-read int|null $tests_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\AdmissionTestOrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereMaximumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereMinimumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereReturnedQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereUserId($value)
 * @mixin \Eloquent
 */
	class AdmissionTestOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string|null $name
 * @property numeric $value
 * @property string|null $start_at
 * @property string|null $stripe_one_time_type_id
 * @property int $synced_one_time_type_to_stripe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AdmissionTestProduct|null $product
 * @method static \Database\Factories\AdmissionTestPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereStripeOneTimeTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereSyncedOneTimeTypeToStripe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereValue($value)
 * @mixin \Eloquent
 */
	class AdmissionTestPrice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $option_name
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property string|null $start_at
 * @property string|null $end_at
 * @property int $quota
 * @property string|null $stripe_id
 * @property bool $synced_to_stripe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AdmissionTestPrice|null $price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTestPrice> $prices
 * @property-read int|null $prices_count
 * @method static \Database\Factories\AdmissionTestProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|AdmissionTestProduct newModelQuery()
 * @method static Builder<static>|AdmissionTestProduct newQuery()
 * @method static Builder<static>|AdmissionTestProduct query()
 * @method static Builder<static>|AdmissionTestProduct whereCreatedAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereEndAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereId($value)
 * @method static Builder<static>|AdmissionTestProduct whereInAgeRange(int|float $age)
 * @method static Builder<static>|AdmissionTestProduct whereInDateRange(\Carbon\Carbon $date)
 * @method static Builder<static>|AdmissionTestProduct whereMaximumAge($value)
 * @method static Builder<static>|AdmissionTestProduct whereMinimumAge($value)
 * @method static Builder<static>|AdmissionTestProduct whereName($value)
 * @method static Builder<static>|AdmissionTestProduct whereOptionName($value)
 * @method static Builder<static>|AdmissionTestProduct whereQuota($value)
 * @method static Builder<static>|AdmissionTestProduct whereStartAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereStripeId($value)
 * @method static Builder<static>|AdmissionTestProduct whereSyncedToStripe($value)
 * @method static Builder<static>|AdmissionTestProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class AdmissionTestProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $interval_month
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property int $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $test
 * @property-read int|null $test_count
 * @method static \Database\Factories\AdmissionTestTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereIntervalMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereMaximumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereMinimumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class AdmissionTestType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\District> $districts
 * @property-read int|null $districts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Area extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $contact_id
 * @property \App\Models\UserHasContact|null $contact
 * @property string $type
 * @property string|null $code
 * @property int $tried_time
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property int $creator_id
 * @property string $creator_ip
 * @property int $middleware_should_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatorIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereMiddlewareShouldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereTriedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereVerifiedAt($value)
 * @mixin \Eloquent
 */
	class ContactHasVerification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $pathname
 * @property string $title
 * @property string|null $og_image_url
 * @property string $description
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\CustomWebPageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereOgImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage wherePathname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class CustomWebPage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $area_id
 * @property string $name
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Area|null $area
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|District whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class District extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Gender extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $admissionTests
 * @property-read int|null $admission_tests_count
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $user_id
 * @property int $number
 * @property string|null $prefix_name
 * @property string|null $nickname
 * @property string|null $suffix_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipOrder> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipTransfer> $transfers
 * @property-read int|null $transfers_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member wherePrefixName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereSuffixName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUserId($value)
 * @mixin \Eloquent
 */
	class Member extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property string $status
 * @property string $expired_at
 * @property int|null $from_year
 * @property int|null $to_year
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read \App\Models\Member|null $member
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereFromYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereToYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereUserId($value)
 * @mixin \Eloquent
 */
	class MembershipOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int $national_mensa_id
 * @property int|null $membership_number
 * @property int|null $membership_ended_in
 * @property string|null $remark
 * @property int|null $is_accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NationalMensa|null $nationalMensa
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereMembershipEndedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereMembershipNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereNationalMensaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereUserId($value)
 * @mixin \Eloquent
 */
	class MembershipTransfer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $master_id
 * @property string $name
 * @property string|null $title
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Module> $children
 * @property-read int|null $children_count
 * @property-read Module|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Module extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $module_id
 * @property int $permission_id
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ModulePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamRole> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	class ModulePermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereUrl($value)
 * @mixin \Eloquent
 */
	class NationalMensa extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $master_id
 * @property string $name
 * @property string|null $url
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NavigationItem> $children
 * @property-read int|null $children_count
 * @property-read NavigationItem|null $parent
 * @method static \Database\Factories\NavigationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereUrl($value)
 * @mixin \Eloquent
 */
	class NavigationItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTestOrder> $admissionTestOrders
 * @property-read int|null $admission_test_orders_count
 * @property-read mixed $type
 * @method static \Database\Factories\OtherPaymentGatewayFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class OtherPaymentGateway extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PassportType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $title
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Module> $modules
 * @property-read int|null $modules_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property string $status
 * @property string $expired_at
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property int $is_returned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read \App\Models\PriorEvidenceResult|null $result
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereIsReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereUserId($value)
 * @mixin \Eloquent
 */
	class PriorEvidenceOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $order_id
 * @property int $test_id
 * @property string $taken_on
 * @property string $score
 * @property numeric|null $percent_of_group
 * @property bool|null $is_accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PriorEvidenceOrder|null $order
 * @property-read \App\Models\QualifyingTest|null $test
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult wherePercentOfGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereTakenOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PriorEvidenceResult extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QualifyingTestDetail> $details
 * @property-read int|null $details_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class QualifyingTest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $test_id
 * @property string|null $taken_from
 * @property string|null $taken_to
 * @property string|null $score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QualifyingTest|null $test
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTakenFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTakenTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class QualifyingTestDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $passport_type_id
 * @property string|null $passport_number
 * @property int|null $user_id
 * @property string $contact_type
 * @property int|null $creator_id
 * @property string $creator_ip
 * @property int $middleware_should_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\PassportType|null $passportType
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatorIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereMiddlewareShouldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog wherePassportTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereUserId($value)
 * @mixin \Eloquent
 */
	class ResetPasswordLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $page_id
 * @property string $name
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SitePage|null $page
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class SiteContent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteContent> $contents
 * @property-read int|null $contents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class SitePage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $type_id
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\TeamType|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int|null $team_id
 * @property int|null $role_id
 * @property int $display_order
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ModulePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole withoutPermission($permissions)
 * @mixin \Eloquent
 */
	class TeamRole extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $title
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TeamType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property string|null $family_name
 * @property string|null $middle_name
 * @property string|null $given_name
 * @property int $gender_id
 * @property int $passport_type_id
 * @property string|null $passport_number
 * @property \Illuminate\Support\Carbon $birthday
 * @property bool $synced_to_stripe
 * @property int|null $address_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PriorEvidenceResult|null $acceptedPriorEvidence
 * @property-read \App\Models\Address|null $address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTestOrder> $admissionTestOrders
 * @property-read int|null $admission_test_orders_count
 * @property-read \App\Models\AdmissionTestHasProctor|\App\Models\AdmissionTestHasCandidate|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $admissionTests
 * @property-read int|null $admission_tests_count
 * @property-read mixed $adorned_name
 * @property-read mixed $age
 * @property-read mixed $age_for_psychology
 * @property-read mixed $can_edit_passport_information
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContactHasVerification> $contactVerifications
 * @property-read int|null $contact_verifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserHasContact> $contacts
 * @property-read int|null $contacts_count
 * @property-read \App\Models\UserHasContact|null $defaultEmail
 * @property-read \App\Models\UserHasContact|null $defaultMobile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserHasContact> $emails
 * @property-read int|null $emails_count
 * @property-read \App\Models\Gender|null $gender
 * @property-read mixed $has_other_same_passport_user_attended_admission_test
 * @property-read mixed $has_other_same_passport_user_joined_future_test
 * @property-read mixed $has_qualification_of_membership
 * @property-read mixed $has_same_passport_already_qualification_of_membership
 * @property-read \App\Models\AdmissionTest|null $lastAdmissionTest
 * @property-read \App\Models\AdmissionTestOrder|null $lastAdmissionTestOrder
 * @property-read mixed $last_attended_admission_test_of_other_same_passport_user
 * @property-read \App\Models\UserLoginLog|null $lastLoginLog
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserLoginLog> $loginLogs
 * @property-read int|null $login_logs_count
 * @property-read \App\Models\Member|null $member
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipTransfer> $memberTransfers
 * @property-read int|null $member_transfers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipOrder> $membershipOrders
 * @property-read int|null $membership_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserHasContact> $mobiles
 * @property-read int|null $mobiles_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\AdmissionTest|null $passedAdmissionTest
 * @property-read \App\Models\PassportType|null $passportType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ModulePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read mixed $preferred_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriorEvidenceOrder> $priorEvidenceOrders
 * @property-read int|null $prior_evidence_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $proctorTests
 * @property-read int|null $proctor_tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamRole> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Library\Stripe\Models\StripeCustomer|null $stripe
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGivenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassportTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSyncedToStripe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $contact
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_verified
 * @property-read \App\Models\ContactHasVerification|null $lastVerification
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContactHasVerification> $verifications
 * @property-read int|null $verifications_count
 * @method static \Database\Factories\UserHasContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereUserId($value)
 * @mixin \Eloquent
 */
	class UserHasContact extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereUserId($value)
 * @mixin \Eloquent
 */
	class UserLoginLog extends \Eloquent {}
}

