<?php
class ModelMultimediaGalleryalbum extends Model {
	public function addAlbum($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "album SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");
		$album_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "album SET image = '" . $this->db->escape($data['image']) . "' WHERE album_id = '" . (int)$album_id . "'");
		}

		foreach ($data['album_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "album_description SET album_id = '" . (int)$album_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");
		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "album_path` SET `album_id` = '" . (int)$album_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");
			$level++;
		}
		$this->db->query("INSERT INTO `" . DB_PREFIX . "album_path` SET `album_id` = '" . (int)$album_id . "', `path_id` = '" . (int)$album_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['album_filter'])) {
			foreach ($data['album_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_filter SET album_id = '" . (int)$album_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

        if (isset($data['album_image'])) {
            foreach ($data['album_image'] as $album_image) {
                $sql = "INSERT INTO " . DB_PREFIX . "album_image SET album_id = '" . (int)$album_id . "', image = '" . $this->db->escape($album_image['image']) . "', sort_order = '" . (int)$album_image['sort_order'] . "'";
                $this->db->query($sql);
                $album_image_id = $this->db->getLastId();
                if (isset($album_image['image_description'])) {
                    foreach ($album_image['image_description'] as $language_id => $value) {
                        $sql = "INSERT INTO " . DB_PREFIX . "album_image_description SET album_image_id = '" . (int)$album_image_id . "', language_id = '" . (int)$language_id . "', label = '" . $this->db->escape($value['label']) . "', description = '" . $this->db->escape($value['description']) . "'";
                        $this->db->query($sql);
                    }
                }

            }
        }

		if (isset($data['album_store'])) {
			foreach ($data['album_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_to_store SET album_id = '" . (int)$album_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['album_seo_url'])) {
			foreach ($data['album_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'album_id=" . (int)$album_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		// Set which layout to use with this album
		if (isset($data['album_layout'])) {
			foreach ($data['album_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_to_layout SET album_id = '" . (int)$album_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('album');
		return $album_id;
	}
	
	public function editAlbum($album_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "album SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE album_id = '" . (int)$album_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "album SET image = '" . $this->db->escape($data['image']) . "' WHERE album_id = '" . (int)$album_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "album_description WHERE album_id = '" . (int)$album_id . "'");
		foreach ($data['album_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "album_description SET album_id = '" . (int)$album_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE path_id = '" . (int)$album_id . "' ORDER BY level ASC");
		if ($query->rows) {
			foreach ($query->rows as $album_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$album_path['album_id'] . "' AND level < '" . (int)$album_path['level'] . "'");
				$path = array();
				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");
				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}
				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$album_path['album_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}
				// Combine the paths with a new level
				$level = 0;
				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "album_path` SET album_id = '" . (int)$album_path['album_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");
					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$album_id . "'");
			// Fix for records with no paths
			$level = 0;
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "album_path` SET album_id = '" . (int)$album_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");
				$level++;
			}
			$this->db->query("REPLACE INTO `" . DB_PREFIX . "album_path` SET album_id = '" . (int)$album_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "album_filter WHERE album_id = '" . (int)$album_id . "'");
		if (isset($data['album_filter'])) {
			foreach ($data['album_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_filter SET album_id = '" . (int)$album_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "album_to_store WHERE album_id = '" . (int)$album_id . "'");
		if (isset($data['album_store'])) {
			foreach ($data['album_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_to_store SET album_id = '" . (int)$album_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

        if (isset($data['album_image'])) {
            foreach ($data['album_image'] as $album_image) {
                $sql = "INSERT INTO " . DB_PREFIX . "album_image SET album_id = '" . (int)$album_id . "', image = '" . $this->db->escape($album_image['image']) . "', sort_order = '" . (int)$album_image['sort_order'] . "'";
                $this->db->query($sql);
                $album_image_id = $this->db->getLastId();
                if (isset($album_image['image_description'])) {
                    foreach ($album_image['image_description'] as $language_id => $value) {
                        $sql = "INSERT INTO " . DB_PREFIX . "album_image_description SET album_image_id = '" . (int)$album_image_id . "', language_id = '" . (int)$language_id . "', label = '" . $this->db->escape($value['label']) . "', description = '" . $this->db->escape($value['description']) . "'";
                        $this->db->query($sql);
                    }
                }

            }
        }

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'album_id=" . (int)$album_id . "'");
		if (isset($data['album_seo_url'])) {
			foreach ($data['album_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'album_id=" . (int)$album_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_to_layout WHERE album_id = '" . (int)$album_id . "'");
		if (isset($data['album_layout'])) {
			foreach ($data['album_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "album_to_layout SET album_id = '" . (int)$album_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('album');
	}
	
	public function deleteAlbum($album_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_path WHERE album_id = '" . (int)$album_id . "'");
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_path WHERE path_id = '" . (int)$album_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteAlbum($result['album_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "album WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_description WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_filter WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_to_store WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_to_layout WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "album_image WHERE album_id = '" . (int)$album_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'album_id=" . (int)$album_id . "'");

		$this->cache->delete('album');
	}
	
	public function repairAlbums($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $album) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$album['album_id'] . "'");
			// Fix for records with no paths
			$level = 0;
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "album_path` WHERE album_id = '" . (int)$parent_id . "' ORDER BY level ASC");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "album_path` SET album_id = '" . (int)$album['album_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");
				$level++;
			}
			$this->db->query("REPLACE INTO `" . DB_PREFIX . "album_path` SET album_id = '" . (int)$album['album_id'] . "', `path_id` = '" . (int)$album['album_id'] . "', level = '" . (int)$level . "'");
			$this->repairAlbums($album['album_id']);
		}
	}
	
	public function getAlbum($album_id) {
		$query = $this->db->query("
			SELECT
				DISTINCT *,
				(SELECT
					GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;')
					FROM
						" . DB_PREFIX . "album_path cp LEFT JOIN
						" . DB_PREFIX . "album_description cd1 ON (cp.path_id = cd1.album_id AND cp.album_id != cp.path_id)
					WHERE
						cp.album_id = c.album_id AND
						cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
					GROUP BY
						cp.album_id) AS path
			FROM
				" . DB_PREFIX . "album c LEFT JOIN
				" . DB_PREFIX . "album_description cd2 ON (c.album_id = cd2.album_id)
			WHERE
				c.album_id = '" . (int)$album_id . "' AND
				cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
	public function getAlbumDescriptions($album_id) {
		$album_description_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_description WHERE album_id = '" . (int)$album_id . "'");
		foreach ($query->rows as $result) {
			$album_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}
		return $album_description_data;
	}
	
	public function getAlbumFilters($album_id) {
		$album_filter_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_filter WHERE album_id = '" . (int)$album_id . "'");
		foreach ($query->rows as $result) {
			$album_filter_data[] = $result['filter_id'];
		}
		return $album_filter_data;
	}
	
	public function getAlbumStores($album_id) {
		$album_store_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_to_store WHERE album_id = '" . (int)$album_id . "'");
		foreach ($query->rows as $result) {
			$album_store_data[] = $result['store_id'];
		}
		return $album_store_data;
	}
	
	public function getAlbumSeoUrls($album_id) {
		$album_seo_url_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'album_id=" . (int)$album_id . "'");
		foreach ($query->rows as $result) {
			$album_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}
		return $album_seo_url_data;
	}
	
	public function getAlbumLayouts($album_id) {
		$album_layout_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_to_layout WHERE album_id = '" . (int)$album_id . "'");
		foreach ($query->rows as $result) {
			$album_layout_data[$result['store_id']] = $result['layout_id'];
		}
		return $album_layout_data;
	}

	public function getAlbumImages($album_id) {
		$images = $this->db->query("SELECT * FROM " . DB_PREFIX . "album_image WHERE album_id = '" . (int)$album_id . "' ORDER BY sort_order ASC");

		$results = array();
		foreach ($images->rows as $image) {
            $descriptions = $this->getAlbumImageDescription($image['album_image_id']);
            foreach ($descriptions as $description) {
                $descr[$description['language_id']] = array(
                    'label' => $description['label'],
                    'description' => $description['description']
                );
            }

			$results[] = array(
				'album_image_id'	=> $image['album_image_id'],
				'album_id'			=> $album_id,
				'image'				=> $image['image'],
				'is_video'			=> $image['is_video'],
				'sort_order'		=> $image['sort_order'],
				'descriptions'		=> $descr
			);
		}

		return $results;
	}

    public function getAlbumImageDescription($album_image_id) {
	    $sql = "SELECT * FROM " . DB_PREFIX . "album_image_description WHERE album_image_id = '" . (int)$album_image_id . "'";
        $query = $this->db->query($sql);
	    return $query->rows;
    }
	
	public function getAlbums($data = array()) {
		$sql = "
			SELECT
				cp.album_id AS album_id,
				c1.status AS status,
				GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name,
				c1.parent_id,
				c1.sort_order
			FROM
				" . DB_PREFIX . "album_path cp LEFT JOIN
				" . DB_PREFIX . "album c1 ON (cp.album_id = c1.album_id) LEFT JOIN
				" . DB_PREFIX . "album c2 ON (cp.path_id = c2.album_id) LEFT JOIN
				" . DB_PREFIX . "album_description cd1 ON (cp.path_id = cd1.album_id) LEFT JOIN
				" . DB_PREFIX . "album_description cd2 ON (cp.album_id = cd2.album_id)
			WHERE
				cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND
				cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'
		";

		if (!empty($data['filter_name'])) { $sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'"; }
		$sql .= " GROUP BY cp.album_id";
		$sort_data = array(
			'name',
			'status',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) { $data['start'] = 0; }
			if ($data['limit'] < 1) { $data['limit'] = 20; }
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalAlbums() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "album");
		return $query->row['total'];
	}
	
	public function getAlbumPath($album_id) {
		$query = $this->db->query("SELECT album_id, path_id, level FROM " . DB_PREFIX . "album_path WHERE album_id = '" . (int)$album_id . "'");
		return $query->rows;
	}
}