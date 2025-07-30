<div class="info-box">
  <span class="info-box-icon bg-info">
    <img src="{{ asset('img/svg/imis-icons/other_building.svg') }}" alt="Residential Icon">
  </span>
  <div class="info-box-content">
    <span class="info-box-text">
      <h3>{{ number_format($directlyDrain) }}</h3>
    </span>
    <span class="info-box-number">{{__('Directly Connected to Drain')}}</span>
  </div>
  <!-- Top-right icon with tooltip -->
  <span class="top-right-icon" data-tooltip="Culture & Religious<br>Agricultural & Farm">
    <i class="fa-solid fa-circle-info"></i>
    <div class="custom-tooltip">{{ __('Drain Network') }}<br>{{ __('Water Body') }} <br> {{ __('Open Ground') }}</div>
  </span>
</div>
