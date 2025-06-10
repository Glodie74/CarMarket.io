<?php include 'includes/header.php'; ?>

<section class="about-hero">
    <div class="container">
        <div class="hero-content">
            <h1>About Eden's Car Shop</h1>
            <p>Your trusted partner in buying and selling quality vehicles since 2010</p>
        </div>
    </div>
</section>

<section class="about-story">
    <div class="container">
        <div class="story-grid">
            <div class="story-content">
                <h2>Our Story</h2>
                <p>Founded in 2010, Eden's Car Shop has been a trusted name in the automotive industry for over a decade. We started with a simple mission: to make car buying and selling transparent, fair, and hassle-free.</p>
                <p>What began as a small local dealership has grown into one of the region's most respected car marketplaces, serving thousands of satisfied customers who trust us with their automotive needs.</p>
                <div class="story-stats">
                    <div class="stat">
                        <h3>13+</h3>
                        <p>Years of Experience</p>
                    </div>
                    <div class="stat">
                        <h3>15,000+</h3>
                        <p>Cars Sold</p>
                    </div>
                    <div class="stat">
                        <h3>98%</h3>
                        <p>Customer Satisfaction</p>
                    </div>
                </div>
            </div>
            <div class="story-image">
                <img src="images/about-story.jpg" alt="Our Story">
                <div class="image-overlay">
                    <div class="play-button">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mission-vision">
    <div class="container">
        <div class="mv-grid">
            <div class="mv-card">
                <div class="mv-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3>Our Mission</h3>
                <p>To revolutionize the car buying and selling experience by providing a transparent, secure, and customer-centric platform that connects buyers and sellers efficiently.</p>
            </div>
            <div class="mv-card">
                <div class="mv-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>To become the most trusted and innovative automotive marketplace, setting new standards for quality, service, and customer satisfaction in the industry.</p>
            </div>
            <div class="mv-card">
                <div class="mv-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Our Values</h3>
                <p>Integrity, transparency, customer focus, and innovation drive everything we do. We believe in building lasting relationships based on trust and mutual respect.</p>
            </div>
        </div>
    </div>
</section>

<section class="team-section">
    <div class="container">
        <div class="section-header">
            <h2>Meet Our Team</h2>
            <p>The passionate professionals behind Eden's Car Shop</p>
        </div>
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image">
                    <img src="images/team1.jpg" alt="John Smith">
                    <div class="member-overlay">
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h4>John Smith</h4>
                    <p class="member-role">Founder & CEO</p>
                    <p class="member-bio">With 15+ years in the automotive industry, John leads our vision of transparent car trading.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <img src="images/team2.jpg" alt="Sarah Johnson">
                    <div class="member-overlay">
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h4>Sarah Johnson</h4>
                    <p class="member-role">Head of Customer Experience</p>
                    <p class="member-bio">Sarah ensures every customer has an exceptional experience from browsing to purchase.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="member-image">
                    <img src="images/team3.jpg" alt="Mike Wilson">
                    <div class="member-overlay">
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h4>Mike Wilson</h4>
                    <p class="member-role">Chief Technology Officer</p>
                    <p class="member-bio">Mike drives our technological innovation, ensuring our platform stays ahead of the curve.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* About Page Styling */
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 120px 0 80px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('images/about-bg.jpg') center/cover;
    opacity: 0.1;
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-content p {
    font-size: 1.3rem;
    opacity: 0.9;
    font-weight: 300;
}

.about-story {
    padding: 100px 0;
    background: white;
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.story-content h2 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 25px;
    font-weight: 600;
    position: relative;
}

.story-content h2::after {
    content: '';
    display: block;
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin-top: 15px;
    border-radius: 2px;
}

.story-content p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #666;
    margin-bottom: 20px;
    text-align: justify;
}

.story-stats {
    display: flex;
    gap: 40px;
    margin-top: 40px;
}

.stat {
    text-align: center;
}

.stat h3 {
    font-size: 2.2rem;
    color: #667eea;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat p {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

.story-image {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.story-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.story-image:hover .image-overlay {
    opacity: 1;
}

.story-image:hover img {
    transform: scale(1.05);
}

.play-button {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.play-button:hover {
    background: white;
    transform: scale(1.1);
}

.play-button i {
    font-size: 1.8rem;
    color: #667eea;
    margin-left: 5px;
}

.mission-vision {
    padding: 100px 0;
    background: #f8f9ff;
}

.mv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.mv-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.mv-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: left 0.3s ease;
}

.mv-card:hover::before {
    left: 0;
}

.mv-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.mv-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    transition: all 0.3s ease;
}

.mv-card:hover .mv-icon {
    transform: scale(1.1) rotate(5deg);
}

.mv-icon i {
    font-size: 2rem;
    color: white;
}

.mv-card h3 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 15px;
    font-weight: 600;
}

.mv-card p {
    color: #666;
    line-height: 1.6;
    text-align: justify;
}

.team-section {
    padding: 100px 0;
    background: white;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-header h2 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 15px;
    font-weight: 600;
    position: relative;
}

.section-header h2::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 20px auto;
    border-radius: 2px;
}

.section-header p {
    font-size: 1.2rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.team-member {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.team-member:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.member-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.member-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.team-member:hover .member-image img {
    transform: scale(1.05);
}

.member-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(102, 126, 234, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.team-member:hover .member-overlay {
    opacity: 1;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    width: 45px;
    height: 45px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: #667eea;
    color: white;
    transform: scale(1.1);
}

.member-info {
    padding: 30px;
    text-align: center;
}

.member-info h4 {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 5px;
    font-weight: 600;
}

.member-role {
    color: #667eea;
    font-weight: 500;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.member-bio {
    color: #666;
    line-height: 1.6;
    font-size: 0.95rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .story-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .story-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .mv-grid,
    .team-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>