<?php
class ControllerMultimediaGalleryAlbum extends Controller {
	private $error = [];
	
	public function index() {
		$this->load->language('multimedia/gallery_album');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('multimedia/gallery_album');
		$this->getList();
	}
	
	public function add() {
		$this->load->language('multimedia/gallery_album');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('multimedia/gallery_album');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_multimedia_gallery_album->addAlbum($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			if (isset($this->request->get['sort']))     { $url .= '&sort=' . $this->request->get['sort']; }
			if (isset($this->request->get['order']))    { $url .= '&order=' . $this->request->get['order']; }
			if (isset($this->request->get['page']))     { $url .= '&page=' . $this->request->get['page']; }

			$this->response->redirect($this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getForm();
	}
	
	public function edit() {
		$this->load->language('multimedia/gallery_album');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('multimedia/gallery_album');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_multimedia_gallery_album->editAlbum($this->request->get['album_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			if (isset($this->request->get['sort']))     { $url .= '&sort=' . $this->request->get['sort']; }
			if (isset($this->request->get['order']))    { $url .= '&order=' . $this->request->get['order']; }
			if (isset($this->request->get['page']))     { $url .= '&page=' . $this->request->get['page']; }

			//$this->response->redirect($this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getForm();
	}
	
	public function delete() {
		$this->load->language('multimedia/gallery_album');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('multimedia/gallery_album');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $album_id) {
				$this->model_multimedia_gallery_album->deleteAlbum($album_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort']))		{ $url .= '&sort=' . $this->request->get['sort']; }
			if (isset($this->request->get['order']))	{ $url .= '&order=' . $this->request->get['order']; }
			if (isset($this->request->get['page']))		{ $url .= '&page=' . $this->request->get['page']; }

			$this->response->redirect($this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}
	
	public function repair() {
		$this->load->language('multimedia/gallery_album');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('multimedia/gallery_album');
		if ($this->validateRepair()) {
			$this->model_multimedia_gallery_album->repairAlbums();
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort']))		{ $url .= '&sort=' . $this->request->get['sort']; }
			if (isset($this->request->get['order'])) 	{ $url .= '&order=' . $this->request->get['order']; }
			if (isset($this->request->get['page']))		{ $url .= '&page=' . $this->request->get['page']; }

			$this->response->redirect($this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}
	
	protected function getList() {
		if (isset($this->request->get['sort']))     { $sort = $this->request->get['sort']; } else { $sort = 'name'; }
		if (isset($this->request->get['order']))    { $order = $this->request->get['order']; } else { $order = 'ASC'; }
		if (isset($this->request->get['page']))     { $page = $this->request->get['page']; } else { $page = 1; }
		$url = '';
		if (isset($this->request->get['sort']))     { $url .= '&sort=' . $this->request->get['sort']; }
		if (isset($this->request->get['order']))    { $url .= '&order=' . $this->request->get['order']; }
		if (isset($this->request->get['page']))     { $url .= '&page=' . $this->request->get['page']; }

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['add'] = $this->url->link('multimedia/gallery_album/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['repair'] = $this->url->link('multimedia/gallery_album/repair', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('multimedia/gallery_album/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$album_total = $this->model_multimedia_gallery_album->getTotalAlbums();
		$results = $this->model_multimedia_gallery_album->getAlbums($filter_data);
		
		$data['albums'] = array();
		foreach ($results as $result) {
			$data['albums'][] = array(
				'album_id'		=> $result['album_id'],
				'name'			=> $result['name'],
				'status'		=> $result['status'],
				'sort_order'	=> $result['sort_order'],
				'edit'			=> $this->url->link('multimedia/gallery_album/edit', 'user_token=' . $this->session->data['user_token'] . '&album_id=' . $result['album_id'] . $url, true),
				'delete'		=> $this->url->link('multimedia/gallery_album/delete', 'user_token=' . $this->session->data['user_token'] . '&album_id=' . $result['album_id'] . $url, true)
			);
		}
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		$url = '';
		if ($order == 'ASC') { $url .= '&order=DESC'; } else { $url .= '&order=ASC'; }
		if (isset($this->request->get['page'])) { $url .= '&page=' . $this->request->get['page']; }

		$data['sort_name'] = $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		$data['sort_status'] = $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$url = '';
		if (isset($this->request->get['sort'])) { $url .= '&sort=' . $this->request->get['sort']; }
		if (isset($this->request->get['order'])) { $url .= '&order=' . $this->request->get['order']; }

		$pagination = new Pagination();
		$pagination->total = $album_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($album_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($album_total - $this->config->get('config_limit_admin'))) ? $album_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $album_total, ceil($album_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('multimedia/galleryalbum_list', $data));
	}
	
	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['album_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) { $data['error'] = $this->error['warning']; } else { $data['error'] = ''; }
		if (isset($this->error['name'])) { $data['error_name'] = $this->error['name']; } else { $data['error_name'] = array(); }
		if (isset($this->error['meta_title'])) { $data['error_meta_title'] = $this->error['meta_title']; } else { $data['error_meta_title'] = array(); }
		if (isset($this->error['keyword'])) { $data['error_keyword'] = $this->error['keyword']; } else { $data['error_keyword'] = ''; }
		if (isset($this->error['parent'])) { $data['error_parent'] = $this->error['parent']; } else { $data['error_parent'] = ''; }
		$url = '';
		if (isset($this->request->get['sort'])) { $url .= '&sort=' . $this->request->get['sort']; }
		if (isset($this->request->get['order'])) { $url .= '&order=' . $this->request->get['order']; }
		if (isset($this->request->get['page'])) { $url .= '&page=' . $this->request->get['page']; }

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		$data['breadcrumbs'][] = array(
			'text' => !isset($this->request->get['album_id']) ? $this->language->get('text_add') : $this->language->get('text_edit') . " #" . $this->request->get['album_id'],
			'href' => ''
		);

		if (!isset($this->request->get['album_id'])) {
			$data['action'] = $this->url->link('multimedia/gallery_album/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('multimedia/gallery_album/edit', 'user_token=' . $this->session->data['user_token'] . '&album_id=' . $this->request->get['album_id'] . $url, true);
		}
		$data['cancel'] = $this->url->link('multimedia/gallery_album', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['album_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$album_info = $this->model_multimedia_gallery_album->getAlbum($this->request->get['album_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['album_description'])) {
			$data['album_description'] = $this->request->post['album_description'];
		} elseif (isset($this->request->get['album_id'])) {
			$data['album_description'] = $this->model_multimedia_gallery_album->getAlbumDescriptions($this->request->get['album_id']);
		} else {
			$data['album_description'] = array();
		}

		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($album_info)) {
			$data['path'] = $album_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($album_info)) {
			$data['parent_id'] = $album_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$this->load->model('catalog/filter');
		if (isset($this->request->post['album_filter'])) {
			$filters = $this->request->post['album_filter'];
		} elseif (isset($this->request->get['album_id'])) {
			$filters = $this->model_multimedia_gallery_album->getAlbumFilters($this->request->get['album_id']);
		} else {
			$filters = array();
		}
		$data['album_filters'] = array();
		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);
			if ($filter_info) {
				$data['category_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		$this->load->model('setting/store');
		$data['stores'] = array();
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['album_store'])) {
			$data['album_store'] = $this->request->post['album_store'];
		} elseif (isset($this->request->get['album_id'])) {
			$data['album_store'] = $this->model_multimedia_gallery_album->getAlbumStores($this->request->get['album_id']);
		} else {
			$data['album_store'] = array(0);
		}

		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($album_info)) {
			$data['image'] = $album_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($album_info) && is_file(DIR_IMAGE . $album_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($album_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Images
		if (isset($this->request->post['album_image'])) {
			$album_images = $this->request->post['album_image'];
		} elseif (isset($this->request->get['album_id'])) {
			$album_images = $this->model_multimedia_gallery_album->getAlbumImages($this->request->get['album_id']);
		} else {
			$album_images = array();
		}

		$data['album_images'] = array();
		foreach ($album_images as $album_image) {
			if (is_file(DIR_IMAGE . $album_image['image'])) {
				$image = $album_image['image'];
				$thumb = $album_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['album_images'][] = array(
				'image'      	=> $image,
				'thumb'      	=> $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' 	=> $album_image['sort_order'],
				'descriptions'	=> $album_image['descriptions'],
			);
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($album_info)) {
			$data['sort_order'] = $album_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($album_info)) {
			$data['status'] = $album_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['album_seo_url'])) {
			$data['album_seo_url'] = $this->request->post['album_seo_url'];
		} elseif (isset($this->request->get['album_id'])) {
			$data['album_seo_url'] = $this->model_multimedia_gallery_album->getAlbumSeoUrls($this->request->get['album_id']);
		} else {
			$data['album_seo_url'] = array();
		}

		if (isset($this->request->post['album_layout'])) {
			$data['album_layout'] = $this->request->post['album_layout'];
		} elseif (isset($this->request->get['album_id'])) {
			$data['album_layout'] = $this->model_multimedia_gallery_album->getAlbumLayouts($this->request->get['album_id']);
		} else {
			$data['album_layout'] = array();
		}

		$this->load->model('design/layout');
		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('multimedia/galleryalbum_form', $data));
	}
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'multimedia/gallery_album')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['album_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if (isset($this->request->get['album_id']) && $this->request->post['parent_id']) {
			$results = $this->model_multimedia_gallery_album->getAlbumPath($this->request->post['parent_id']);
			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->get['album_id']) {
					$this->error['parent'] = $this->language->get('error_parent');
					break;
				}
			}
		}

		if ($this->request->post['album_seo_url']) {
			$this->load->model('design/seo_url');
			foreach ($this->request->post['album_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['album_id']) || ($seo_url['query'] != 'album_id=' . $this->request->get['album_id']))) {		
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
								break;
							}
						}
					}
				}
			}
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			//$this->error['warning'] = $this->language->get('error_warning');
		}
		
		return !$this->error;
	}
	
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'multimedia/gallery_album')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
	
	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'multimedia/gallery_album')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function autocomplete() {
		$json = array();
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('multimedia/gallery_album');
			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);
			$results = $this->model_multimedia_gallery_album->getAlbums($filter_data);
			foreach ($results as $result) {
				$json[] = array(
					'album_id'	=> $result['album_id'],
					'name'		=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}