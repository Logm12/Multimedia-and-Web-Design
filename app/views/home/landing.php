<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Healthcare System'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($BASE_URL); ?>/assets/css/landing-style.css"> <!-- Đổi tên file CSS nếu bạn muốn -->
    <!-- Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts (ví dụ: Poppins hoặc một font hiện đại khác) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
</head>
<body>

    <header id="main-header">
        <div class="container header-container">
            <a href="<?php echo htmlspecialchars($BASE_URL); ?>/" class="logo">
                <img src="<?php echo htmlspecialchars($BASE_URL); ?>/assets/images/icon.jpg" alt="PulseCare Logo">
                <span>PulseCare</span> <!-- Hoặc tên hệ thống của bạn -->
            </a>
            <nav id="main-nav">
                <ul>
                    <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/">Home</a></li>
                    <li><a href="#about-us-section">About</a></li>
                    <li><a href="#medical-services-section">Services</a></li>
                    <li><a href="#best-doctors-section">Doctors</a></li>
                    <li><a href="#contact-section">Contact</a></li>
                </ul>
            </nav>
<a href="<?php echo htmlspecialchars($BASE_URL); ?>/auth/login" class="btn btn-primary btn-make-appointment">Make Appointment</a>
            <button id="mobile-menu-toggle"><i class="fas fa-bars"></i></button> <!-- Nút cho mobile menu -->
        </div>
    </header>

    <main>
        <section id="hero-section">
            <div class="container hero-container">
                <div class="hero-content">
                    <p class="hero-welcome-text">WELCOME TO MEDCARE</p>
                    <h1>Taking care of <br>your health is our <br>top priority.</h1>
                    <p class="hero-description">
                        Being healthy is more than just not getting sick. It entails mental, physical,
                        and social well-being. It’s not just about treatment, it’s about healing.
                    </p>
                     <a href="<?php echo htmlspecialchars($BASE_URL); ?>/auth/login" class="btn btn-secondary btn-book-hero">Book An Appointment</a>
                </div>
                <div class="hero-image-container">
                    <!-- Thay bằng ảnh bác sĩ của bạn -->
                    <img src="<?php echo htmlspecialchars($BASE_URL); ?>/assets/images/doctor.png" alt="Doctor providing care">
                </div>
            </div>
        </section>

        <section id="info-strip-section">
            <div class="container info-strip-container">
                <div class="info-item">
                    <div class="info-icon-container">
                        <i class="fas fa-user-md fa-2x"></i> <!-- Icon bác sĩ -->
                    </div>
                    <h3>Qualified Doctors</h3>
                    <p>Developing whole individuals is our goal. We have a flexible, high-trust environment.</p>
                </div>
                <div class="info-item">
                     <div class="info-icon-container">
                        <i class="fas fa-helicopter fa-2x"></i> <!-- Icon trực thăng -->
                    </div>
                    <h3>Emergency Helicopter</h3>
                    <p>The air ambulance feature is now available even to middle class people, saving lives.</p>
                </div>
                <div class="info-item">
                    <div class="info-icon-container">
                        <i class="fas fa-lungs-virus fa-2x"></i> <!-- Icon Covid -->
                    </div>
                    <h3>Covid - 19</h3>
                    <p>With rising Covid-19 cases, it is hard to imagine a positive start to your date.</p>
                </div>
            </div>
        </section>

        <!-- Các section khác sẽ thêm vào đây (About Us, Services, etc.) -->
          <section id="about-us-section">
            <div class="container about-us-container">
                <div class="about-us-content" data-aos="fade-up" data-aos-delay="100">
                    <p class="section-eyebrow"data-aos="fade-up" data-aos-delay="200" data-aos-duration="500">
                        <span class="eyebrow-icon">
                            <svg data-aos="fade-up" data-aos-delay="200" width="30" height="10" viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 5C1.48235 2.0625 3.23529 1.25 5.25882 2.0625C7.28235 2.875 8.54118 5 10.4118 5S12.8824 2.5 14.7059 2.5S17.1765 5.875 19.2353 5.875C21.2941 5.875 22.8824 2.5 24.8235 2.5C26.7647 2.5 28.2588 3.75 30 5" stroke="#00AEEF" stroke-width="2"/></svg>
                        </span>
                        ABOUT US
                    </p>
                    <h2 data-aos="fade-up" data-aos-delay="200">Welcome To Medcare <br>Central Hospital</h2>
                    <p class="about-description" data-aos="fade-up" data-aos-delay="200" data-aos-duration="500">
                        Our system is built with the patient at the center. We understand that health is your most valuable asset, and seeking medical care should not be a burden. Our platform connects you with a dedicated team of doctors and nurses, while providing you with tools to proactively monitor and manage your health information in a transparent and efficient way.
                    </p>
                    <ul class="about-features-list" data-aos="fade-up" data-aos-delay="200">
                        <li><i class="fas fa-check-circle"></i> 15+ Years of excellence</li>
                        <li><i class="fas fa-check-circle"></i> 24/7 Hour Medical Service</li>
                        <li><i class="fas fa-check-circle"></i> A Multispecialty hospital</li>
                        <li><i class="fas fa-check-circle"></i> A team of professionals</li>
                    </ul>
                    <a href="<?php echo htmlspecialchars($BASE_URL); ?>/auth/login" class="btn btn-secondary btn-book-about">Book An Appointment</a>
                </div>
                <div class="about-us-image-container">
                    <!-- Thay bằng ảnh bác sĩ của bạn -->
                    <img src="<?php echo htmlspecialchars($BASE_URL); ?>/assets/images/aaa.jpg" alt="Doctor welcoming">
                    <div class="decorative-shape shape-1"></div>
                    <div class="decorative-shape shape-2"></div>
                </div>
            </div>
        </section>
         <section id="stats-strip-section">
            <div class="container stats-strip-container">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <span class="stat-number">35+</span>
                    <p class="stat-label">National Awards</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <span class="stat-number">125+</span>
                    <p class="stat-label">Expert Doctors</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <span class="stat-number">5k+</span>
                    <p class="stat-label">Satisfied Patients</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                    <span class="stat-number">8k+</span>
                    <p class="stat-label">Operation Success</p>
                </div>
            </div>
        </section>
        <section id="medical-services-section">
            <div class="container">
                <div class="section-header text-center"> <!-- Class để căn giữa tiêu đề section -->
                    <p class="section-eyebrow">
                        <span class="eyebrow-icon">
                            <svg width="30" height="10" viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 5C1.48235 2.0625 3.23529 1.25 5.25882 2.0625C7.28235 2.875 8.54118 5 10.4118 5S12.8824 2.5 14.7059 2.5S17.1765 5.875 19.2353 5.875C21.2941 5.875 22.8824 2.5 24.8235 2.5C26.7647 2.5 28.2588 3.75 30 5" stroke="#00AEEF" stroke-width="2"/></svg>
                        </span>
                        MEDICAL SERVICES
                    </p>
                    <h2>Find Out More About <br>Our Services</h2>
                </div>

                <div class="services-grid">
                   <?php
                // Dữ liệu mẫu cho các service cards (Tiếng Anh)
                $services = [
                    [
                        'title' => 'Cardiology',
                        'icon' => 'fas fa-heart-pulse', // Or 'fas fa-heartbeat'
                        'description' => 'Comprehensive diagnosis and treatment of cardiovascular diseases, from hypertension and arrhythmias to coronary artery disease and heart failure.',
                        'link' => BASE_URL . '/services/cardiology',
                    ],
                    [
                        'title' => 'Neurology',
                        'icon' => 'fas fa-brain',
                        'description' => 'Examination and treatment for conditions related to the central and peripheral nervous system, including stroke, headaches, epilepsy, and Parkinson\'s disease.',
                        'link' => BASE_URL . '/services/neurology',
                        'is_highlighted' => true, // Highlighted card
                    ],
                    [
                        'title' => 'Urology',
                        // 'icon' => 'fas fa-toilet-paper', // Still a bit odd
                        'icon' => 'fas fa-user-doctor', // A more general medical icon, or 'fas fa-clinic-medical'
                        // If you have a custom SVG or image for a kidney/bladder symbol, that would be best.
                        'description' => 'Care for urinary tract health issues in both men and women, including infections, kidney stones, and prostate conditions.',
                        'link' => BASE_URL . '/services/urology',
                    ],
                    [
                        'title' => 'Pulmonary Medicine', // Or just 'Pulmonary'
                        'icon' => 'fas fa-lungs',
                        'description' => 'Diagnosis and treatment of lung and respiratory diseases such as asthma, COPD, pneumonia, and sleep-related breathing disorders.',
                        'link' => BASE_URL . '/services/pulmonary',
                    ],
                    [
                        'title' => 'Radiology',
                        'icon' => 'fas fa-x-ray', // Or 'fas fa-radiation-alt' for a different style
                        'description' => 'Providing advanced diagnostic imaging services including X-rays, ultrasounds, CT scans, and MRIs for early and accurate disease detection.',
                        'link' => BASE_URL . '/services/radiology',
                    ],
                    [
                        'title' => 'Psychotherapy', // Or 'Mental Health Services'
                        'icon' => 'fas fa-comment-medical', // Or 'fas fa-brain', 'fas fa-users' (for group therapy)
                        'description' => 'Supportive counseling and therapy for psychological issues, stress, anxiety, and depression, improving mental well-being and quality of life.',
                        'link' => BASE_URL . '/services/psychotherapy',
                    ]
                ];
                ?>

                <!-- Phần lặp qua $services giữ nguyên -->
                <?php foreach ($services as $index => $service): ?>
                <div class="service-card <?php echo isset($service['is_highlighted']) && $service['is_highlighted'] ? 'highlighted' : ''; ?>"
                    data-aos="fade-up" data-aos-delay="<?php echo ($index % 3 + 1) * 100; ?>">
                    <div class="service-icon-container">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?> fa-2x"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($service['link']); ?>" class="read-more-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php endforeach; ?>

                </div>
            </div>
        </section>

      <section id="testimonials-section">
    <div class="container testimonials-main-container">

        <!-- CỘT BÊN TRÁI: Nội dung và Nút điều hướng -->
        <div class="testimonial-content-column">
            <div class="section-header text-left">
                <p class="section-eyebrow" data-aos="fade-up" data-aos-delay="100">
                    <span class="eyebrow-icon">
                        <svg width="30" height="10" viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 5C1.48235 2.0625 3.23529 1.25 5.25882 2.0625C7.28235 2.875 8.54118 5 10.4118 5S12.8824 2.5 14.7059 2.5S17.1765 5.875 19.2353 5.875C21.2941 5.875 22.8824 2.5 24.8235 2.5C26.7647 2.5 28.2588 3.75 30 5" stroke="#00AEEF" stroke-width="2"/></svg>
                    </span>
                    TESTIMONIALS
                </p>
                <h2 data-aos="fade-up" data-aos-delay="200">Great Patient Stories</h2>
                <p class="section-description" data-aos="fade-up" data-aos-delay="200" >
                    Discover how our dedicated care has made a difference in the lives of our patients. Your health journey is our priority.
                </p>
            </div>
            <div class="testimonial-navigation-custom">
                <button class="testimonial-nav-prev custom-swiper-button"><i class="fas fa-chevron-left"></i></button>
                <button class="testimonial-nav-next custom-swiper-button"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

        <!-- CỘT BÊN PHẢI: Swiper cho Testimonials -->
        <div class="testimonial-slider-column" data-aos="fade-up" data-aos-delay="300">
            <div class="testimonial-slider-container">
                <div class="swiper testimonial-swiper">
                    <div class="swiper-wrapper"> <!-- MỞ Ở ĐÂY -->
                        <?php
                        $testimonials = [
                            [
                                'quote' => "The care I received was exceptional. The doctors were knowledgeable and compassionate, and the online booking system made everything so convenient. I truly felt listened to and well-cared for throughout my treatment.",
                                'author_name' => 'Sarah L.',
                                'author_title' => 'Recovered Patient',
                                'author_avatar' => $BASE_URL . '/assets/images/patient1.jpg'
                            ],
                            [
                                'quote' => "Using this platform to manage my appointments and medical records has been a game-changer. It's user-friendly, secure, and I can access my health information anytime. Highly recommended!",
                                'author_name' => 'John B.',
                                'author_title' => 'Regular User',
                                'author_avatar' => $BASE_URL . '/assets/images/patient2.jpg'
                            ],
                            [
                                'quote' => "From the initial consultation booking to follow-up care, the entire process was seamless. The medical team is professional, and the system is incredibly efficient. Thank you for making healthcare accessible.",
                                'author_name' => 'Maria K.',
                                'author_title' => 'New Mother',
                                'author_avatar' => $BASE_URL . '/assets/images/patient3.jpg'
                            ]
                        ];
                        ?>
                        <?php foreach ($testimonials as $testimonial): ?>
                        <div class="swiper-slide">
                            <div class="testimonial-card">
                                <p class="testimonial-quote">"<?php echo htmlspecialchars($testimonial['quote']); ?>"</p>
                                <div class="testimonial-author">
                                    <img src="<?php echo htmlspecialchars($testimonial['author_avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['author_name']); ?>" class="author-avatar">
                                    <div class="author-info">
                                        <span class="author-name"><?php echo htmlspecialchars($testimonial['author_name']); ?></span>
                                        <span class="author-title"><?php echo htmlspecialchars($testimonial['author_title']); ?></span>
                                    </div>
                                </div>
                                <div class="quote-icon-container">
                                    <i class="fas fa-quote-right"></i>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div> <!-- ĐÓNG swiper-wrapper Ở ĐÂY -->
                </div> <!-- ĐÓNG testimonial-swiper -->
                <!-- Bạn có thể thêm các nút nav mặc định của Swiper ở đây nếu muốn test -->
                <!-- <div class="swiper-button-prev"></div> -->
                <!-- <div class="swiper-button-next"></div> -->
            </div> <!-- ĐÓNG testimonial-slider-container -->
        </div> <!-- ĐÓNG testimonial-slider-column -->

    </div> <!-- ĐÓNG testimonials-main-container -->
</section>

           <section id="work-process-section">
            <div class="container">
                <div class="section-header text-center">
                    <p class="section-eyebrow">
                        <span class="eyebrow-icon">
                             <svg width="30" height="10" viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 5C1.48235 2.0625 3.23529 1.25 5.25882 2.0625C7.28235 2.875 8.54118 5 10.4118 5S12.8824 2.5 14.7059 2.5S17.1765 5.875 19.2353 5.875C21.2941 5.875 22.8824 2.5 24.8235 2.5C26.7647 2.5 28.2588 3.75 30 5" stroke="#00AEEF" stroke-width="2"/></svg>
                        </span>
                        OUR WORK PROCESS
                    </p>
                    <h2>Let's See How We Work</h2>
                    <p class="section-description">
                        We've streamlined our processes to ensure you receive efficient, effective, and compassionate healthcare every step of the way.
                    </p>
                </div>

                <div class="work-process-grid">
                    <?php
                    $work_steps = [
                        [
                            'icon' => 'fas fa-user-check', // Hoặc 'fas fa-calendar-check-o' cho việc đặt lịch
                            'title' => '1. Easy Appointment Booking',
                            'description' => 'Quickly find available doctors and schedule your visit online at your convenience, anytime, anywhere.'
                        ],
                        [
                            'icon' => 'fas fa-stethoscope', // Hoặc 'fas fa-notes-medical'
                            'title' => '2. Thorough Diagnosis',
                            'description' => 'Our experienced medical professionals conduct comprehensive assessments to accurately identify your health concerns.'
                        ],
                        [
                            'icon' => 'fas fa-pills', // Hoặc 'fas fa-prescription-bottle-alt'
                            'title' => '3. Personalized Treatment',
                            'description' => 'Receive a tailored treatment plan designed to address your specific needs and promote a swift recovery.'
                        ]
                    ];
                    ?>
                    <?php foreach ($work_steps as $index => $step): ?>
                    <div class="process-step-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 150; ?>">
                        <div class="process-icon-container">
                            <i class="<?php echo htmlspecialchars($step['icon']); ?> fa-2x"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($step['title']); ?></h3>
                        <p><?php echo htmlspecialchars($step['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

         <section id="best-doctors-section">
            <div class="container">
                <div class="section-header-doctors"> <!-- Header riêng cho section này để đặt nút nav -->
                    <div class="section-header-doctors-content">
                        <p class="section-eyebrow">
                            <span class="eyebrow-icon">
                                <svg width="30" height="10" viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 5C1.48235 2.0625 3.23529 1.25 5.25882 2.0625C7.28235 2.875 8.54118 5 10.4118 5S12.8824 2.5 14.7059 2.5S17.1765 5.875 19.2353 5.875C21.2941 5.875 22.8824 2.5 24.8235 2.5C26.7647 2.5 28.2588 3.75 30 5" stroke="#00AEEF" stroke-width="2"/></svg>
                            </span>
                            OUR DOCTORS
                        </p>
                        <h2>Our Best Doctors</h2>
                    </div>
                    <div class="doctors-navigation-custom">
                        <button class="doctor-nav-prev custom-swiper-button-v2"><i class="fas fa-chevron-left"></i></button>
                        <button class="doctor-nav-next custom-swiper-button-v2"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="doctors-slider-container">
                    <div class="swiper doctors-swiper">
                        <div class="swiper-wrapper">
                            <?php
                            // Dữ liệu mẫu cho bác sĩ - nên lấy từ database sau này
                            $doctors_data = [
                                [
                                    'name' => 'Dr. Duy', 'specialty' => 'Cardiologist',
                                    'image' => $BASE_URL . '/assets/images/doctor1.jpg',
                                    'social' => ['facebook' => '#', 'twitter' => '#', 'whatsapp' => '#']
                                ],
                                [
                                    'name' => 'Dr. Duc', 'specialty' => 'Cardiologist',
                                    'image' => $BASE_URL . '/assets/images/doctor2.jpg',
                                    'social' => ['facebook' => 'https://facebook.com/dr.tracy', 'twitter' => 'https://twitter.com/dr.tracy', 'whatsapp' => 'https://wa.me/1234567890']
                                ],
                                [
                                    'name' => 'Dr. Long', 'specialty' => 'Neurologist',
                                    'image' => $BASE_URL . '/assets/images/hoho.avif',
                                    'social' => ['facebook' => '#', 'twitter' => '#', 'whatsapp' => '#']
                                ],
                                [
                                    'name' => 'Dr. Thien', 'specialty' => 'Pediatrician',
                                    'image' => $BASE_URL . '/assets/images/doctor8.jpg',
                                    'social' => ['facebook' => '#', 'twitter' => '#', 'whatsapp' => '#']
                                ],
                                [
                                    'name' => 'Dr. Van Tran', 'specialty' => 'Dermatologist',
                                    'image' => $BASE_URL . '/assets/images/doctor7.jpg',
                                    'social' => ['facebook' => '#', 'twitter' => '#', 'whatsapp' => '#']
                                ],
                                [
                                    'name' => 'Dr. Ngoc Anh', 'specialty' => 'Oncologist',
                                    'image' => $BASE_URL . '/assets/images/doctor6.jpg', // Cần thêm ảnh này
                                    'social' => ['facebook' => '#', 'twitter' => '#', 'whatsapp' => '#']
                                ]
                            ];
                            ?>
                            <?php foreach ($doctors_data as $doctor): ?>
                            <div class="swiper-slide">
                                <div class="doctor-card" data-aos="fade-up" data-aos-delay="100">
                                    <div class="doctor-image-container">
                                        <img src="<?php echo htmlspecialchars($doctor['image']); ?>" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                                    </div>
                                    <div class="doctor-info">
                                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                                        <div class="doctor-social-links">
                                            <a href="<?php echo htmlspecialchars($doctor['social']['facebook']); ?>" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                            <a href="<?php echo htmlspecialchars($doctor['social']['twitter']); ?>" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                                            <a href="<?php echo htmlspecialchars($doctor['social']['whatsapp']); ?>" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

   <footer id="main-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <!-- Cột 1: Thông tin chung -->
                    <div class="footer-column about-column">
                        <h3 class="footer-logo"><span>Pulse</span>Care</h3> <!-- Hoặc logo bằng ảnh -->
                        <p class="footer-about-text">
                            Providing accessible and efficient healthcare solutions through technology. Our commitment is to your well-being and a seamless medical experience.
                        </p>
                        <ul class="footer-contact-info">
                            <li><i class="fas fa-envelope"></i> <a href="mailto:info@axiscare.com">info@pulsecare.com</a></li>
                            <li><i class="fas fa-map-marker-alt"></i> 123 Health St, Wellness City, HC 54321</li>
                        </ul>
                    </div>

                    <!-- Cột 2: Quick Links -->
                    <div class="footer-column links-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/#about-us-section">About Us</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/#medical-services-section">Our Services</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/#testimonials-section">Testimonials</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/blogs">Our Blogs</a></li> <!-- Giả sử có trang blogs -->
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/contact">Contact Us</a></li> <!-- Giả sử có trang contact -->
                        </ul>
                    </div>

                    <!-- Cột 3: Support/Legal -->
                    <div class="footer-column links-column">
                        <h4>Support</h4>
                        <ul>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/terms-of-use">Terms of Use</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/privacy-policy">Privacy Policy</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/contact-support">Contact Support</a></li>
                            <li><a href="<?php echo htmlspecialchars($BASE_URL); ?>/careers">Careers</a></li>
                        </ul>
                    </div>

                    <!-- Cột 4: Book Appointment / Contact -->
                    <div class="footer-column contact-action-column">
                        <h4>Book An Appointment</h4>
                        <p>Our dedicated staff are ready to assist you. Schedule your consultation today.</p>
                        <a href="tel:+0123456789" class="footer-phone-button">
                            <i class="fas fa-phone-alt"></i> Call: +012 345 6789
                        </a>
                        <!-- Hoặc một link đến trang đặt lịch -->
                        <!-- <a href="<?php echo htmlspecialchars($BASE_URL); ?>/auth/login" class="btn btn-primary" style="margin-top:10px;">Book Online</a> -->
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-container">
                <p class="copyright-text">© <?php echo date('Y'); ?> PulseCare. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="<?php echo htmlspecialchars($BASE_URL); ?>/terms-conditions">Terms & Conditions</a>
                    <span>|</span>
                    <a href="<?php echo htmlspecialchars($BASE_URL); ?>/privacy-policy">Privacy Policy</a>
                </div>
                <div class="footer-social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <!-- <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a> -->
                </div>
            </div>
        </div>
    </footer>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
  AOS.init({
    duration: 800, // thời gian animation (ms)
    once: true // chỉ animate một lần
  });
</script>
<script>
    // JavaScript đơn giản cho mobile menu toggle
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.getElementById('main-nav');
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
    const animatedElements = document.querySelectorAll(
    '.about-us-content, .about-us-image-container, .stat-item, .service-card'
);
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // ... (code menu, intersection observer) ...

    const testimonialSwiper = new Swiper('.testimonial-swiper', {
        loop: true,
        slidesPerView: 1, // Mặc định hiển thị 1 slide
        spaceBetween: 15, // Khoảng cách giữa các slide khi slidesPerView > 1
        centeredSlides: true, // QUAN TRỌNG: Slide active sẽ ở giữa
        grabCursor: true,
        effect: 'coverflow', // Thử hiệu ứng này nếu muốn giống mẫu hơn
        coverflowEffect: {
             rotate: 0, // Không xoay
             stretch: 50, // Khoảng cách kéo dãn giữa các slide
             depth: 150, // Độ sâu 3D
             modifier: 1, // Tốc độ hiệu ứng
             slideShadows: false, // Bỏ đổ bóng của Swiper
         },
 // THÊM CẤU HÌNH AUTOPLAY VÀO ĐÂY
        autoplay: {
            delay: 3000, // Thời gian giữa các lần chuyển slide (ms), ví dụ 5 giây
            disableOnInteraction: false, // Tiếp tục autoplay sau khi người dùng tương tác (ví dụ: vuốt)
                                        // Đặt là true nếu muốn dừng autoplay khi người dùng tương tác
            pauseOnMouseEnter: true,    // Tạm dừng autoplay khi chuột di vào slider (tùy chọn)
        },

    breakpoints: {
            // Màn hình tablet
            768: {
                slidesPerView: 'auto', // Cho phép Swiper tự tính toán dựa trên kích thước card và spaceBetween
                                     // Hoặc đặt một giá trị cụ thể như 1.6
                spaceBetween: 20
            },
            // Màn hình desktop
            1024: {
                slidesPerView: 'auto', // Hoặc 2.2, 2.5
                                     // Với 'auto', bạn cần đặt width cho .swiper-slide hoặc .testimonial-card
                                     // để Swiper biết kích thước của mỗi slide.
                                     // Nếu .testimonial-card có max-width, Swiper có thể dựa vào đó.
                spaceBetween: 30
            }
        },

        // Sử dụng các nút tùy chỉnh bạn đã tạo
        navigation: {
            nextEl: '.testimonial-nav-next',
            prevEl: '.testimonial-nav-prev',
        },
    });
     // --- Doctors Slider Logic ---
    const doctorsSwiperInstance = new Swiper('.doctors-swiper', {
        loop: true, // Bỏ loop nếu số lượng slide không nhiều hơn slidesPerView nhiều
        slidesPerView: 1, // Default cho mobile
        spaceBetween: 20,
        grabCursor: true,

        autoplay: { // Tùy chọn nếu muốn tự động trượt
           delay: 4000,
           disableOnInteraction: false,
         },

        navigation: {
            nextEl: '.doctor-nav-next',
            prevEl: '.doctor-nav-prev',
        },

        breakpoints: {
            // when window width is >= 576px
            576: {
                slidesPerView: 2,
                spaceBetween: 20
            },
            // when window width is >= 768px
            768: {
                slidesPerView: 3,
                spaceBetween: 25
            },
            // when window width is >= 992px
            992: {
                slidesPerView: 4, // Hiển thị 4 bác sĩ như mẫu
                spaceBetween: 30
            }
        }
    });

});

    // Thêm sự kiện click cho các nút điều hướng
    const prevButton = document.querySelector('.testimonial-nav-prev');
    const nextButton = document.querySelector('.testimonial-nav-next');

    if (prevButton && nextButton) {
        prevButton.addEventListener('click', function() {
            testimonialSwiper.slidePrev();
        });

        nextButton.addEventListener('click', function() {
            testimonialSwiper.slideNext();
        });
    }
</script>
</body>
</html>