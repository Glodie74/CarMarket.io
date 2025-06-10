<?php
include("includes/db.php");
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="search-container">
        <header class="search-header">
            <h1><i class="fas fa-search"></i> Search Results</h1>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </header>

        <div class="search-content">
            <?php
            if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
                $search_query = htmlspecialchars(trim($_GET['query']), ENT_QUOTES, 'UTF-8');
                $query = "%" . $conn->real_escape_string($_GET['query']) . "%";
                $stmt = $conn->prepare("SELECT * FROM products WHERE brand LIKE ? OR make LIKE ? OR title LIKE ?");
                $stmt->bind_param("sss", $query, $query, $query);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    echo "<div class='results-header'>";
                    echo "<h2>Found " . $result->num_rows . " results for '<span class='search-term'>" . $search_query . "</span>'</h2>";
                    echo "</div>";
                    echo "<div class='car-grid'>";
                    
                    while ($row = $result->fetch_assoc()):
            ?>
                <div class="car-card">
                    <div class="car-image">
                        <img src="uploads/<?= htmlspecialchars($row['image1'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="price-tag">$<?= number_format($row['price'], 2) ?></div>
                    </div>
                    <div class="car-details">
                        <h3><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="car-meta">
                            <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="fas fa-road"></i> <?= number_format($row['mileage']) ?> km</span>
                            <span><i class="fas fa-cogs"></i> <?= htmlspecialchars($row['transmission'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="car-info">
                            <p><strong>Make:</strong> <?= htmlspecialchars($row['make'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Brand:</strong> <?= htmlspecialchars($row['brand'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <p class="description"><?= substr(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'), 0, 100) ?>...</p>
                        <div class="car-actions">
                            <button class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-secondary">
                                <i class="fas fa-heart"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                    endwhile;
                    echo "</div>";
                else:
                    echo "<div class='no-results'>";
                    echo "<i class='fas fa-search-minus'></i>";
                    echo "<h2>No cars found</h2>";
                    echo "<p>No cars matched your search for '<span class='search-term'>" . $search_query . "</span>'. Please try different keywords.</p>";
                    echo "<a href='index.php' class='btn btn-primary'>Browse All Cars</a>";
                    echo "</div>";
                endif;
            } else {
                echo "<div class='no-results'>";
                echo "<i class='fas fa-exclamation-triangle'></i>";
                echo "<h2>No search term provided</h2>";
                echo "<p>Please enter a search term to find cars.</p>";
                echo "<a href='index.php' class='btn btn-primary'>Go Back</a>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .search-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-header h1 {
            color: #333;
            font-size: 2.2rem;
            font-weight: 700;
        }

        .search-header h1 i {
            color: #667eea;
            margin-right: 15px;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .search-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .results-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .results-header h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .search-term {
            color: #667eea;
            font-weight: 700;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .car-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .car-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .car-card:hover .car-image img {
            transform: scale(1.05);
        }

        .price-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .car-details {
            padding: 20px;
        }

        .car-details h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .car-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .car-meta span {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .car-meta i {
            color: #667eea;
        }

        .car-info {
            margin: 15px 0;
        }

        .car-info p {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .description {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
        }

        .car-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
        }

        .no-results i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        .no-results h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .no-results p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .search-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .search-header h1 {
                font-size: 1.8rem;
            }

            .car-grid {
                grid-template-columns: 1fr;
            }

            .car-meta {
                justify-content: center;
            }

            .car-actions {
                flex-direction: column;
            }
        }
    </style>

    <script src="assetscript.js"></script>
</body>
</html>
