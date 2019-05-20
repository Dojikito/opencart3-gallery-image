<?php
class ControllerMultimediaGalleryAlbum extends Controller {
	private $breadcrumbs_builded = array();
	
	public function index() {
		$this->load->language('multimedia/gallery_album');
		$this->load->model('multimedia/gallery_album');
		$this->load->model('tool/image');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/albums.css');
        $this->document->addStyle('catalog/view/javascript/lightgallery/css/lightgallery.css');

        $this->document->addScript('catalog/view/javascript/lightgallery/js/lightgallery.js');
        $this->document->addScript('catalog/view/javascript/jquery.mousewheel.min.js');
        $this->document->addScript('catalog/view/javascript/picturefill.min.js');

		if (isset($this->request->get['filter']))   { $filter = $this->request->get['filter']; } else { $filter = ''; }
		if (isset($this->request->get['sort']))     { $sort = $this->request->get['sort']; } else { $sort = 'p.sort_order'; }
		if (isset($this->request->get['order']))    { $order = $this->request->get['order']; } else { $order = 'ASC'; }
		if (isset($this->request->get['page']))     { $page = $this->request->get['page']; } else { $page = 1; }
		if (isset($this->request->get['limit']))    { $limit = (int)$this->request->get['limit']; } else { $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'); }
		if (isset($this->request->get['album_id'])) { $album_id = $this->request->get['album_id']; }	else { $album_id = 0; }
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => 'Albums',
			'href' => $this->url->link('multimedia/gallery_album')
		);
		
		$child_albums = $this->model_multimedia_gallery_album->getAlbums($album_id);
		
		if ($album_id) {
			$album_info = $this->model_multimedia_gallery_album->getAlbum($album_id);
			$images = $this->model_multimedia_gallery_album->getImages($album_id);
			$parents = $this->model_multimedia_gallery_album->getParents($album_id);
			$this->breadcrumbBuilder($parents);
			foreach ($this->breadcrumbs_builded as $breadcrumb) {
				$data['breadcrumbs'][] = array(
					'text' => $breadcrumb['name'],
					'href' => $breadcrumb['href']
				);
			}
		} else {
			$images = 0;
			$album_info = 0;
		}
		
		$data['child_albums'] = array();
		$data['album_info'] = array();
		
		if ($child_albums) {
			foreach ($child_albums as $album) {
				if ($album['image']) {
					$thumb = $this->model_tool_image->resize($album['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$thumb = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}
				
				$data['child_albums'][] = array(
					'name'			=> $album['name'],
					'description'	=> utf8_substr(trim(strip_tags(html_entity_decode($album['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'href'			=> $this->url->link('multimedia/gallery_album', 'album_id=' . $album['album_id']),
					'thumb'			=> $thumb,
					'date_added'	=> $album['date_added']
				);
			}
		}
		
		if ($album_info) {
			$this->document->setTitle($album_info['meta_title']);
			$this->document->setDescription($album_info['meta_description']);
			$this->document->setKeywords($album_info['meta_keyword']);
			
			if ($album_info['image']) {
				$popup = $this->model_tool_image->resize($album_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_album_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_album_image_popup_height'));
			} else {
				$popup = '';
			}

			if ($album_info['image']) {
				$thumb = $this->model_tool_image->resize($album_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$thumb = '';
			}
			
			$data['album_info'] = array(
				'name'			=> $album_info['name'],
				'description'	=> html_entity_decode($album_info['description'], ENT_QUOTES, 'UTF-8'),
				'date_added'	=> $album_info['date_added'],
				'popup'			=> $popup,
				'thumb'			=> $thumb
			);
			
			$data['images'] = array();
			foreach ($images as $image) {
			    $res_str = $this->model_tool_image->resize($image['image'], 375, 250) . ' 375' . $this->model_tool_image->resize($image['image'], 480, 320) . ' 480' . $this->model_tool_image->resize($image['image'], 800, 535) . ' 800';
				$data['images'][] = array(
					'thumb'         => $this->model_tool_image->resize($image['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_image_thumb_height')),
					'popup'         => $this->model_tool_image->resize($image['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_gallery_image_popup_height')),
                    'original'      => $image['image'],
                    'respons'       => $res_str,
                    'label'         => $image['label'],
                    'description'   => $image['description']
				);
			}

		} else {
			$this->document->setTitle('Albums');
			$this->document->setDescription('meta description');
			$this->document->setKeywords('meta keywords');
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('multimedia/gallery_album', $data));
	}
	
	private function breadcrumbBuilder($parent = array()) {
		if ($parent) {
			foreach ($parent as $p => $it) {
				if ($it['child']) { $this->breadcrumbBuilder($it['child']); }
				$this->breadcrumbs_builded[] = array(
					'name'	=> $it['name'],
					'href'	=> $this->url->link('multimedia/gallery_album', 'album_id=' . $it['album_id'])
				);
			}
		}
	}
}