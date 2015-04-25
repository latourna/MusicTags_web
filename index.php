<?php define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME'])); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Music Tags</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Cache-Control" content="Public">
        <meta name="description" content="Find music tags and high-definition covers from the iTunes and Spotify APIs.">
        <link charset="utf-8" rel="stylesheet" type="text/css" href="style.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="js/jquery.inview.min.js"></script>
    </head>
    <body>
        <a href="<?php echo WEBROOT ?>"><h1>Music Tags</h1></a>
        <form method="GET" action="" class="searchbar">
            <input class="text-sb" type="text" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : '' ?>" placeholder="Search an artist, an album or a song title">
            <select name="type">
                <option value="song" <?php if (isset($_GET['type']) and $_GET['type']=="song") echo 'selected' ?>>Song</option>
                <option value="album" <?php if (isset($_GET['type']) and $_GET['type']=="album") echo 'selected' ?>>Album</option>
            </select>
            <select name="country">
                <option value="fr" <?php if (isset($_GET['country']) and $_GET['country']=="fr") echo 'selected' ?>>France</option>
                <option value="de" <?php if (isset($_GET['country']) and $_GET['country']=="de") echo 'selected' ?>>Germany</option>
                <option value="it" <?php if (isset($_GET['country']) and $_GET['country']=="it") echo 'selected' ?>>Italy</option>
                <option value="jp" <?php if (isset($_GET['country']) and $_GET['country']=="jp") echo 'selected' ?>>Japan</option>
                <option value="es" <?php if (isset($_GET['country']) and $_GET['country']=="es") echo 'selected' ?>>Spain</option>
                <option value="gb" <?php if (isset($_GET['country']) and $_GET['country']=="gb") echo 'selected' ?>> United Kingdom</option>
                <option value="us" <?php if (!isset($_GET['country']) or (isset($_GET['country']) and $_GET['country']=="us")) echo 'selected' ?>> United States</option>
            </select>
            <select name="limit">
                <option value="20" <?php if (isset($_GET['limit']) and $_GET['limit']=="20") echo 'selected' ?>>Limit : 20</option>
                <option value="50" <?php if (isset($_GET['limit']) and $_GET['limit']=="50") echo 'selected' ?>>Limit : 50</option>
                <option value="100" <?php if (isset($_GET['limit']) and $_GET['limit']=="100") echo 'selected' ?>>Limit : 100</option>
                <option value="200" <?php if (isset($_GET['limit']) and $_GET['limit']=="200") echo 'selected' ?>>Limit : 200</option>
            </select>
            <select name="source">
                <option value="iTunes" <?php if (isset($_GET['source']) and $_GET['source']=="iTunes") echo 'selected' ?>>iTunes</option>
                <option value="Spotify" <?php if (isset($_GET['source']) and $_GET['source']=="Spotify") echo 'selected' ?>>Spotify</option>
            </select>
            <input class="button-sb" type="submit" value="Go !">
        </form>
        <?php
        $is_song = false;
        if (isset($_GET['search'])) {
            $term = $_GET['search'];
            if (isset($_GET['country'])) {
                $country = $_GET['country'];
            } else {
                $country = "fr";
            }
            if (isset($_GET['limit'])) {
                $limit = $_GET['limit'];
            } else {
                $limit = "20";
            }
            if (isset($_GET['source'])) {
                $source = $_GET['source'];
            } else {
                $source = 'iTunes';
            }
            if ($source == "iTunes") {
                if (isset($_GET['type'])) {
                    $type = $_GET['type'];
                } else {
                    $type = "song";
                }
                if (isset($_GET['searchMode'])) {
                    $searchMode = ($_GET['searchMode'] == 'id') ? 'lookup?id' : 'search?term';
                } else {
                    $searchMode = 'search?term';
                }
            } else if ($source == "Spotify") {
                if ($limit > 50) {
                    $limit = 50;
                }
                if (isset($_GET['type']))
                    if ($_GET['type'] == "song") {
                        $type = "track";
                    }
                    else {
                        $type = $_GET['type'];
                    }
                else {
                    $type = "track";
                }
                if (isset($_GET['searchMode'])) {
                    if ($type == 'album') {
                        $searchMode = 'artists';
                    } else {
                        $searchMode = 'albums';
                    }
                }
            }
        }
        else {
            $term = "";
            $type = "";
            $country = "";
            $limit = "";
        }
        if (isset($_GET['search']) and !empty($_GET['search'])) {
            $term = str_replace(" ", "%20", $term);
            if ($source == "iTunes") {
                $url = 'http://itunes.apple.com/' . $searchMode . '=' . $term . '&entity=' . $type . '&country=' . $country . '&limit=' . $limit;
            }
            else if ($source == "Spotify") {
                if (!isset($_GET['searchMode'])) {
                    $url = 'https://api.spotify.com/v1/search?' . 'query=' . $term . '&type=' . $type . '&market=' . $country . '&limit=' . $limit;
                } else {
                    if ($type == 'album') {
                        $url = 'https://api.spotify.com/v1/' . $searchMode . '/' . $term . '/' . $type . 's' . '?market=' . $country;
                    } else {
                        $url = 'https://api.spotify.com/v1/' . $searchMode . '/' . $term;
                    }
                }
            }
            $json = file_get_contents($url);
            $parsed_json = json_decode($json);
        ?>
        <table class="results-table">
            <thead>
                <tr class="table-row">
                </tr>
            </thead>
            <tbody>
                <?php
                if ($source == "iTunes") {
                    if ($type == "song") {
                        $is_song = true;
                        $audio_format = "mp4";
                    }
                    if (isset($_GET['searchMode']) and $_GET['searchMode'] == 'id') {
                        array_splice($parsed_json->results, 0, 1);
                        $parsed_json->resultCount--;
                    }
                    $value = $parsed_json->results;
                    foreach ($value as $key => $o) {
                        $o->artwork = new stdClass();
                        $o->artwork->{'200x200'} = str_replace("100x100", "200x200", $o->artworkUrl100);
                        $o->artwork->{'400x400'} = str_replace("100x100", "400x400", $o->artworkUrl100);
                        $o->artwork->{'600x600'} = str_replace("100x100", "600x600", $o->artworkUrl100);
                        $o->artwork->{'1200x1200'} = str_replace("100x100", "1200x1200", $o->artworkUrl100);
                    }
                }
                else if ($source = "Spotify") {
                    if ($type == "track") {
                        $is_song = true;
                        $audio_format = "mpeg";
                        $value = $parsed_json->tracks;
                        $value = $value->items;
                        foreach ($value as $key => $o) {
                            $o->artwork = new stdClass();
                            $o->trackCensoredName = $o->name;
                            $o->trackViewUrl = $o->external_urls->spotify;
                            if (isset($_GET['searchMode']) and $_GET['searchMode'] == 'id') {
                                $o->collectionCensoredName = $parsed_json->name;
                                $o->artistName = $parsed_json->artists[0]->name;
                                $o->artistId = $parsed_json->artists[0]->id;
                                $o->artworkUrl100 = $parsed_json->images[1]->url;
                                $o->collectionId = $parsed_json->id;
                                $o->artwork->{'64x64'} = $parsed_json->images[2]->url;
                                $o->artwork->{'300x300'} = $parsed_json->images[1]->url;
                                $o->artwork->{'640x640'} = $parsed_json->images[0]->url;
                                $o->releaseDate = $parsed_json->release_date;
                            } else {
                                $o->collectionCensoredName = $o->album->name;
                                $o->artistName = $o->artists[0]->name;
                                $o->artistId = $o->artists[0]->id;
                                $o->artworkUrl100 = $o->album->images[1]->url;
                                $o->collectionId = $o->album->id;
                                $o->artwork->{'64x64'} = $o->album->images[2]->url;
                                $o->artwork->{'300x300'} = $o->album->images[1]->url;
                                $o->artwork->{'640x640'} = $o->album->images[0]->url;
                            }
                            $o->discNumber = $o->disc_number;
                            $o->trackNumber = $o->track_number;
                            $o->trackTimeMillis = $o->duration_ms;
                            $o->previewUrl = $o->preview_url;
                            $trackCount[$o->disc_number] = $o->track_number;
                        }
                        foreach ($value as $k => $o) {
                            $o->discCount = $value[$key]->disc_number;
                            $o->trackCount = $trackCount[$o->disc_number];
                        }
                    } else if ($type == "album") {
                        $is_song = false;
                        $value = $parsed_json;
                        if (!isset($_GET['searchMode'])) {
                            $value = $parsed_json->albums;
                        }
                        $value = $value->items;
                        foreach ($value as $key => $o) {
                            $json = file_get_contents($o->href);
                            $r = json_decode($json);
                            $o->collectionCensoredName = $r->name;
                            $o->artistName = $r->artists[0]->name;
                            $o->artistId = $r->artists[0]->id;
                            $o->artworkUrl100 = $r->images[1]->url;
                            $o->collectionId = $r->id;
                            $o->releaseDate = $r->release_date;
                            $o->trackNumber = $r->tracks->total;
                            $o->artwork = new stdClass();
                            $o->artwork->{'64x64'} = $r->images[2]->url;
                            $o->artwork->{'300x300'} = $r->images[1]->url;
                            $o->artwork->{'640x640'} = $r->images[0]->url;
                        }
                    } else if ($type == "artist") {
                        $value = $parsed_json->artists;
                    }
                }
                foreach ($value as $i => $value) { ?>
                <tr class="table-row i<?php echo $i%2 ?>">
                <td class="artwork flexible-col">
                    <img alt="<?php echo $value->collectionCensoredName ?>, <?php echo $value->artistName ?>" class="artwork" src="<?php echo str_replace("100x100", "200x200", $value->artworkUrl100) ?>">
                    <?php if ($is_song) { ?>
                    <audio preload="none">
                        <source src="<?php echo $value->previewUrl ?>" type="audio/mp4">
                    </audio>
                    <span class="play" tabindex="<?php echo $i ?>">
                        <span class="play-bg"></span>
                        <span class="play-icon"></span>
                    </span>
                    <?php } ?>
                </td>
                <td class="description flexible-col">
                    <ul class="list">
                        <?php if (isset($value->artistName)) { ?>
                        <li class = "artistName"> <?php echo($value->artistName != "Various Artists" ? '<a href="' . '?search=' . $value->artistId . '&type=album&country='. $country .'&limit=200&source=' . $source . '&searchMode=id" class="artistName">' . $value->artistName . '</a>' : '<span class = "artistName">' . $value->artistName . '</span>') ?> </li>
                        <?php } if (isset($value->collectionCensoredName)) { ?>
                        <li class="collectionCensoredName"><a href="<?php echo '?search=' . $value->collectionId . '&type=song&country='. $country .'&limit=200&source=' . $source . '&searchMode=id' ?>" class="collectionCensoredName"><?php echo $value->collectionCensoredName ?></a></li>
                        <?php } if (isset($value->trackCensoredName)) { ?>
                        <li class="trackCensoredName"><a href="<?php echo $value->trackViewUrl ?>" target=_blank class="trackCensoredName"><?php echo $value->trackCensoredName ?></a></li>
                        <?php } if (isset($value->releaseDate)) { ?>
                        <li class="releaseDate"><span><?php echo substr($value->releaseDate, 0, 10) ?></span></li>
                        <?php } if (isset($value->trackTimeMillis)) { ?>
                        <li class="trackTimeMillis"><span class="trackTimeMillis"><?php echo date("i:s", $value->trackTimeMillis/1000) ?></span></li>
                        <?php } if (isset($value->discNumber)and isset($value->discCount)) { ?>
                        <li class="discNumber"><?php echo $value->discNumber . '/'; echo $value->discCount ?></span></li>
                        <?php } else if (isset($value->discNumber)) { ?>
                        <li class="discNumber"><?php echo $value->discNumber ?></span></li>
                        <?php } if (isset($value->trackNumber) and isset($value->trackCount)) { ?>
                        <li class="trackNumber"><span class="trackNumber"><?php echo $value->trackNumber . '/' . $value->trackCount ?></span></li>
                        <?php } else if (isset($value->trackNumber)) { ?>
                        <li class="trackNumber"><span class="trackNumber"><?php echo $value->trackNumber ?></span></li>
                        <?php } else if (isset($value->trackCount)) { ?>
                        <li class="trackCount"><span class="trackCount"><?php echo $value->trackCount ?></span></li>
                        <?php } else if (isset($value->primaryGenreName)) { ?>
                        <li class="primaryGenreName"><span class="primaryGenreName"><?php echo $value->primaryGenreName ?></span></li>
                        <?php } ?>
                        <li class="covers_links">
                            <span>Album cover : </span>
                            <?php foreach ($value->{"artwork"} as $key => $value) { ?>
                            <a href="<?php echo $value ?>" target=_blank class="artworkUrl200"><?php echo $key ?></a>
                            <?php } ?>
                        </li>
                    </ul>
                </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php }
        else {
            echo '<h3 class="welcome_message">Enter an artist, an album or a song title to find the tags.</h3>';
        }
        ?>
        <script>
            $('.table-row').bind('inview', function(event, visible) {
                if (visible) {
                    $(this).stop().animate({opacity: 1}, 400);
                }
            });
        </script>
        <?php if ($is_song) { ?>
        <script src="js/audioplayer.js"></script>
        <?php } ?>
    </body>
</html>