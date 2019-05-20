<?php
class ModelMultimediaGalleryAlbum extends Model {
	public function getAlbums($parent_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "album c LEFT JOIN " . DB_PREFIX . "album_description cd ON (c.album_id = cd.album_id) LEFT JOIN " . DB_PREFIX . "album_to_store c2s ON (c.album_id = c2s.album_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");
		return $query->rows;
	}
	
	public function getAlbum($album_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "album c LEFT JOIN " . DB_PREFIX . "album_description cd ON (c.album_id = cd.album_id) LEFT JOIN " . DB_PREFIX . "album_to_store c2s ON (c.album_id = c2s.album_id) WHERE c.album_id = '" . (int)$album_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");
		return $query->row;
	}
	
	public function getImages($album_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "album_image ai LEFT JOIN " . DB_PREFIX . "album_image_description aid ON (ai.album_image_id = aid.album_image_id) WHERE ai.album_id = '" . (int)$album_id . "' AND aid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ai.sort_order ASC";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getParentName($album_id) {
		$sql = "SELECT name FROM " . DB_PREFIX . "album_description WHERE album_id = '" . (int)$album_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		return $query->row['name'];
	}
	
	public function getParents($album_id) {
		$parents = array();
		if ($album_id != 0) {
			$sql = "SELECT ad.name, a.parent_id FROM " . DB_PREFIX . "album a LEFT JOIN " . DB_PREFIX . "album_description ad ON (a.album_id = ad.album_id) WHERE a.album_id = '" . (int)$album_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$result = $this->db->query($sql)->row;
			
			$parents[] = array(
				'album_id'	=> $album_id,
				'name'		=> $result['name'],
				'child'		=> $this->getParents($result['parent_id'])
			);
		}
		return $parents;
	}
}