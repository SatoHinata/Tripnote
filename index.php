<?php
session_start();
require_once 'config/config.php';

$is_logged_in = isset($_SESSION['user_id']);

// 並べ替え条件
$order = $_GET['sort'] ?? 'newest';
switch ($order) {
    case 'oldest':
        $order_sql = "ORDER BY posts.created_at ASC";
        break;
    case 'title':
        $order_sql = "ORDER BY posts.title ASC";
        break;
    case 'country':
        $order_sql = "ORDER BY posts.country_code ASC";
        break;
    case 'visited_newest':
        $order_sql = "ORDER BY posts.trip_date DESC";
        break;
    case 'visited_oldest':
        $order_sql = "ORDER BY posts.trip_date ASC";
        break;
    case 'random':
        $order_sql = "ORDER BY RAND()";
        break;
    case 'continent':
        $order_sql = "
            ORDER BY FIELD(posts.country_code,
                'JP','KR','CN','SG','MY','PH','ID','TH','VN','IN',
                'FR','GB','DE','IT','ES','PT','GR','CH','NL',
                'US','CA','MX','BR',
                'EG','ZA','MA','KE',
                'AU','NZ','GU','FJ'
            ), posts.country_code
        ";
        break;
    default:
        $order_sql = "ORDER BY posts.created_at DESC";
}

$search = $_GET['search'] ?? '';
$where_sql = '';
$params = [];

if (!empty($search)) {
    $where_sql = "WHERE posts.title LIKE :search OR posts.location LIKE :search OR posts.country_code LIKE :search";
    $params[':search'] = "%$search%";
}

$sql = "SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.user_id $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->query("SELECT DISTINCT UPPER(country_code) FROM posts WHERE country_code IS NOT NULL");
$countries = $stmt2->fetchAll(PDO::FETCH_COLUMN);

$flags = [
    'JP'=>'🇯🇵','KR'=>'🇰🇷','CN'=>'🇨🇳','SG'=>'🇸🇬','MY'=>'🇲🇾',
    'PH'=>'🇵🇭','ID'=>'🇮🇩','TH'=>'🇹🇭','VN'=>'🇻🇳','IN'=>'🇮🇳',
    'QA'=>'🇶🇦','AE'=>'🇦🇪','SA'=>'🇸🇦',
    'AU'=>'🇦🇺','NZ'=>'🇳🇿','GU'=>'🇬🇺','FJ'=>'🇫🇯',
    'FR'=>'🇫🇷','GB'=>'🇬🇧','DE'=>'🇩🇪','IT'=>'🇮🇹','ES'=>'🇪🇸',
    'PT'=>'🇵🇹','GR'=>'🇬🇷','CH'=>'🇨🇭','NL'=>'🇳🇱',
    'US'=>'🇺🇸','CA'=>'🇨🇦','MX'=>'🇲🇽','BR'=>'🇧🇷',
    'EG'=>'🇪🇬','ZA'=>'🇿🇦','MA'=>'🇲🇦','KE'=>'🇰🇪',
    'HI'=>'🌺'
];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Tripnote</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
body { font-family:'Roboto',sans-serif;font-weight:300;margin:0;background:#fff;color:#222;}
header {
    position: fixed;
    top: 0;
    width: 100%;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eaeaea;
    z-index: 1000;
    box-sizing: border-box;
    flex-wrap: wrap;
}

header h1 {
    font-size: 22px;
    font-weight: 300;
    margin: 0;
    flex-shrink: 0;
}

nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
    flex: 1;
}

nav a {
    font-size: 14px;
    color: #222;
    text-decoration: none;
    padding: 6px 8px;
    border-radius: 4px;
    transition: 0.2s;
    white-space: nowrap;
}

nav a:hover {
    background: #f5f5f5;
}
#map {width:100%;height:400px;margin-top:70px;}
main {max-width:1000px;margin:20px auto;padding:0 15px;}
h2 {font-weight:300;margin-bottom:15px;}
.sort-bar {display:flex;justify-content:flex-end;margin-bottom:15px;}
.sort-bar select {font-size:14px;padding:5px;}
.post-list {display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;}
.post-card {background:#fff;border:1px solid #eaeaea;border-radius:8px;padding:15px;transition:0.3s;}
.post-card:hover {transform:translateY(-2px);}
.post-card img {width:100%;height:180px;object-fit:cover;border-radius:6px;margin-bottom:10px;}
footer {text-align:center;padding:20px;font-size:12px;color:#888;border-top:1px solid #eaeaea;margin-top:40px;}
#filter-info {text-align:center;margin:10px;font-size:18px;font-weight:300;}
</style>
</head>
<body>
<header>
    <h1>Tripnote</h1>
    <nav>
        <a href="index.php">Home</a>
        <?php if ($is_logged_in): ?>
            <a href="post.php">Post</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<div id="map"></div>
<div id="filter-info"></div>
<main>
    <h2>Posts</h2>
    <div class="sort-bar" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <!-- 検索フォーム -->
        <form method="get" action="index.php" style="display:flex;gap:8px;">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" 
                   style="padding:6px 10px;font-size:14px;border:1px solid #ccc;border-radius:4px;"/>
            <button type="submit" style="padding:6px 12px;font-size:14px;border:1px solid #ccc;background:#fff;cursor:pointer;">Search</button>
            <?php if(!empty($search)): ?>
                <a href="index.php" style="padding:6px 10px;font-size:14px;border:1px solid #ccc;background:#fff;text-decoration:none;">Reset</a>
            <?php endif; ?>
        </form>
    
        <!-- 並べ替えフォーム -->
        <form method="get" action="index.php" style="display:flex;gap:8px;">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            <label for="sort">Sort by: </label>
            <select name="sort" id="sort" onchange="this.form.submit()" style="padding:5px;">
                <option value="newest" <?php if($order=='newest') echo 'selected'; ?>>Newest</option>
                <option value="oldest" <?php if($order=='oldest') echo 'selected'; ?>>Oldest</option>
                <option value="title" <?php if($order=='title') echo 'selected'; ?>>Title (A-Z)</option>
                <option value="country" <?php if($order=='country') echo 'selected'; ?>>Country (A-Z)</option>
                <option value="continent" <?php if($order=='continent') echo 'selected'; ?>>Continent Order</option>
                <option value="random" <?php if($order=='random') echo 'selected'; ?>>Random</option>
                <option value="visited_newest" <?php if($order=='visited_newest') echo 'selected'; ?>>Visited Date (Newest)</option>
                <option value="visited_oldest" <?php if($order=='visited_oldest') echo 'selected'; ?>>Visited Date (Oldest)</option>
            </select>
        </form>
    </div>
    <div class="post-list">
        <?php foreach ($posts as $post): ?>
            <div class="post-card" data-country="<?php echo htmlspecialchars($post['country_code']); ?>">
                <?php if ($post['image_path']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($post['image_path']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <p><?php echo htmlspecialchars($post['location']); ?> <?php echo $flags[$post['country_code']] ?? ''; ?></p>
                <p>Visited: <?php echo htmlspecialchars($post['trip_date']); ?></p>
                <p>by <?php echo htmlspecialchars($post['name']); ?></p>
                <div class="action-buttons">
                    <a href="detail.php?post_id=<?php echo $post['post_id']; ?>">View</a>
                    <?php if ($is_logged_in && $_SESSION['user_id'] == $post['user_id']): ?>
                        <a href="edit.php?post_id=<?php echo $post['post_id']; ?>">Edit</a>
                        <a href="delete.php?post_id=<?php echo $post['post_id']; ?>" onclick="return confirm('Delete this post?');">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer>&copy; 2025 Tripnote. All rights reserved.</footer>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var map = L.map('map', { center:[10,0], zoom:2.3, minZoom:1, maxZoom:19 });
document.getElementById('map').style.background = '#dcdcdc';
var southWest = L.latLng(-60, -180);
var northEast = L.latLng(75, 540);
map.setMaxBounds(L.latLngBounds(southWest, northEast));

var visitedCountries = <?php echo json_encode($countries); ?>;
var flags = <?php echo json_encode($flags); ?>;
var isoMap = {
    "JPN":"JP","KOR":"KR","CHN":"CN","SGP":"SG","MYS":"MY","PHL":"PH","IDN":"ID","THA":"TH","VNM":"VN","IND":"IN",
    "QAT":"QA","ARE":"AE","SAU":"SA","AUS":"AU","NZL":"NZ","GUM":"GU","FJI":"FJ","FRA":"FR","GBR":"GB","DEU":"DE","ITA":"IT","ESP":"ES",
    "PRT":"PT","GRC":"GR","CHE":"CH","NLD":"NL","USA":"US","CAN":"CA","MEX":"MX","BRA":"BR","EGY":"EG","ZAF":"ZA","MAR":"MA","KEN":"KE"
};

var highlightedLayer = null;

function filterPostsByCountry(countryCode) {
    var posts = document.querySelectorAll('.post-card');
    posts.forEach(function(post) {
        post.style.display = (post.dataset.country === countryCode) ? 'block' : 'none';
    });

    if (highlightedLayer) map.removeLayer(highlightedLayer);
    highlightedLayer = L.geoJson(null, { style:{color:'#ff0000',weight:3,fillOpacity:0} });

    fetch('https://raw.githubusercontent.com/johan/world.geo.json/master/countries.geo.json')
    .then(res => res.json())
    .then(data => {
        var feature = data.features.find(f => isoMap[f.id] === countryCode);
        if (feature) {
            var countryName = feature.properties.name;
            highlightedLayer.addData(feature).addTo(map);
            map.fitBounds(L.geoJson(feature).getBounds(), { maxZoom:5 });
            document.getElementById('filter-info').innerHTML = `Showing posts from ${flags[countryCode] || ''} <strong>${countryName}</strong>`;
        }
    });

    if (!document.getElementById('resetFilter')) {
        var resetBtn = document.createElement('button');
        resetBtn.id = 'resetFilter';
        resetBtn.textContent = 'Show All';
        resetBtn.style.cssText = 'margin:10px;padding:8px 12px;border:1px solid #ccc;background:#fff;cursor:pointer;';
        resetBtn.onclick = function() {
            posts.forEach(p => p.style.display = 'block');
            document.getElementById('filter-info').innerHTML = '';
            if (highlightedLayer) map.removeLayer(highlightedLayer);
            map.setView([10,0],2.3);
            this.remove();
        };
        document.querySelector('main').insertBefore(resetBtn, document.querySelector('.post-list'));
    }
}

fetch('https://raw.githubusercontent.com/johan/world.geo.json/master/countries.geo.json')
.then(res => res.json())
.then(data => {
    const dataPlus = JSON.parse(JSON.stringify(data));
    const dataMinus = JSON.parse(JSON.stringify(data));
    dataPlus.features = dataPlus.features.map(f => shiftFeature(f,360));
    dataMinus.features = dataMinus.features.map(f => shiftFeature(f,-360));
    [dataMinus,data,dataPlus].forEach(dataset=>{
        L.geoJson(dataset,{style:f=>({fillColor:'#333',fillOpacity:0.9,color:'#dcdcdc',weight:1})}).addTo(map);
        L.geoJson(dataset,{
            filter:f=>{
                var iso2 = isoMap[f.id];
                return iso2 && visitedCountries.includes(iso2);
            },
            style:f=>({fillColor:'#fff',fillOpacity:0.95,color:'#111',weight:1.5}),
            onEachFeature:(f,layer)=>{
                var iso2 = isoMap[f.id];
                if(iso2 && visitedCountries.includes(iso2)){
                    var center = layer.getBounds().getCenter();
                    var flag = flags[iso2]||'';
                    var marker = L.marker(center,{icon:L.divIcon({className:'flag-icon',html:'<span>'+flag+'</span>'})}).addTo(map);
                    marker.on('click',()=>filterPostsByCountry(iso2));
                }
            }
        }).addTo(map);
    });
});

function shiftFeature(feature, shift){
    if(feature.geometry.type==="Polygon"){
        feature.geometry.coordinates=feature.geometry.coordinates.map(ring=>ring.map(coord=>[coord[0]+shift,coord[1]]));
    }else if(feature.geometry.type==="MultiPolygon"){
        feature.geometry.coordinates=feature.geometry.coordinates.map(polygon=>polygon.map(ring=>ring.map(coord=>[coord[0]+shift,coord[1]])));
    }
    return feature;
}
</script>
</body>
</html>