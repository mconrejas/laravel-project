<div class="modal-header">
	<h5 class="modal-title" id="exampleModalLongTitle">
		{{ __('User information') }}
	</h5>
</div>
<div class="modal-body">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('KYC Type') }}
                        <span>{{ ucwords($info->type) ?? 'Personal' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('ID type') }}
                        <span>{{ ucwords($info->id_type) ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('ID Number') }}
                        <span>{{ $info->id_number ?? 'none' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('First Name') }}
                        <span>{{ $info->first_name ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Last name') }}
                        <span>{{ $info->last_name ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Nationality') }}
                        <span>{{ $info->countryNationality ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Birth Date') }}
                        <span>{{ $info->date_of_birth ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Street Address') }}
                        <span>{{ $info->street_address ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Street Address 2') }}
                        <span>{{ $info->street_address2 ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('City') }}
                        <span>{{ $info->city ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('State') }}
                        <span>{{ $info->state ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Postal Code') }}
                        <span>{{ $info->postal_code ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Country') }}
                        <span>{{ $info->country ?? '' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Contact Number') }}
                        <span>{{ $info->contact_number ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('KYC Status') }}
                        <span>{{ $info->getStatus() ?? 'Pending' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-1">
                        {{ __('Submitted At') }}
                        <span>{{ $info->created_at ?? '' }}</span>
                    </li>
                </ul>
			</div>
		</div>
	</div>
</div>