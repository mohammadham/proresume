    @if (is_array($userPermissions) && in_array('Experience', $userPermissions))

        <!--====== Experience Section Start ======-->
        <section class="experience-section pb-lg-90 pb-50 pt-lg-100 section-gap" id="experience">
            <div class="container">
                <div class="text-center">
                    <p class="text-primary mb-10" data-aos="fade-up" data-aos-delay="100">
                        {{ $home_text->experience_title ?? __('Experience') }}
                    </p>
                    <h2 class="mb-30" data-aos="fade-up" data-aos-delay="200">
                        {{ $home_text->experience_subtitle ?? $keywords['Experience'] }}
                    </h2>
                </div>

                <div class="experience-wrapper boxed-wrapper">
                    <div class="row justify-content-between">
                        @if (count($educations) > 0)
                            <div class="col-lg-6 md-gap-80">
                                <h4 class="experience-wrapper-title">
                                    <span><img class="lazy"
                                            data-src="{{ asset('assets/front/img/profile1/education.png') }}"
                                            alt=""></span>
                                    {{ $keywords['Education'] ?? __('Education') }}
                                </h4>
                                <div class="experience-list">
                                    @foreach ($educations as $education)
                                        <div class="single-experience">
                                            <h5 class="title">{{ $education->degree_name }}</h5>
                                            <span class="duration">
                                                {{ \Carbon\Carbon::parse($education->start_date)->format('M j, Y') }}
                                                -
                                                @if (!empty($education->end_date))
                                                    {{ \Carbon\Carbon::parse($education->end_date)->format('M j, Y') }}
                                                @else
                                                    {{ $keywords['Present'] ?? 'Present' }}
                                                @endif
                                            </span>
                                            <p>{!! nl2br($education->short_description) !!}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if (count($job_experiences) > 0)
                            <div class="col-lg-6">
                                <h4 class="experience-wrapper-title">
                                    <span><img class="lazy"
                                            data-src="{{ asset('assets/front/img/profile1/job.png') }}"
                                            alt=""></span>
                                    {{ $keywords['Job'] ?? 'Job' }}
                                </h4>
                                <div class="experience-list">
                                    @foreach ($job_experiences as $job_experience)
                                        <div class="single-experience">
                                            <h5 class="title">{{ $job_experience->designation }}
                                                [{{ $job_experience->company_name }}]</h5>
                                            <span class="duration">
                                                {{ \Carbon\Carbon::parse($job_experience->start_date)->format('M j, Y') }}
                                                -
                                                @if ($job_experience->is_continue == 0)
                                                    {{ \Carbon\Carbon::parse($job_experience->end_date)->format('M j, Y') }}
                                                @else
                                                    {{ $keywords['Present'] ?? 'Present' }}
                                                @endif
                                            </span>
                                            <p>{!! nl2br($job_experience->content) !!}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        <!--====== Experience Section End ======-->
    @endif
