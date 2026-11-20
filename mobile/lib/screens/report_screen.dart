import 'dart:io';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';

const List<String> risquesDisponibles = [
  'Inondation',
  'Erosion',
  'Eboulement',
  'Effondrement',
];

class ReportScreen extends StatefulWidget {
  const ReportScreen({super.key});

  @override
  State<ReportScreen> createState() => _ReportScreenState();
}

class _ReportScreenState extends State<ReportScreen> {
  final _formKey = GlobalKey<FormState>();
  final _quartierController = TextEditingController();
  final _descriptionController = TextEditingController();

  List<Map<String, dynamic>> _communes = [];
  int? _communeId;
  String? _risque;
  DateTime? _date;
  double? _latitude;
  double? _longitude;
  File? _file;

  bool _loadingCommunes = true;
  bool _loadingLocation = false;
  bool _submitting = false;
  String? _loadError;

  @override
  void initState() {
    super.initState();
    _loadCommunes();
    _captureLocation();
  }

  Future<void> _loadCommunes() async {
    try {
      final communes = await ApiService.fetchCommunes();
      setState(() {
        _communes = communes;
        _loadingCommunes = false;
      });
    } catch (e) {
      setState(() {
        _loadError = 'Impossible de charger la liste des communes.';
        _loadingCommunes = false;
      });
    }
  }

  Future<void> _captureLocation() async {
    setState(() => _loadingLocation = true);
    try {
      final permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        final requested = await Geolocator.requestPermission();
        if (requested == LocationPermission.denied ||
            requested == LocationPermission.deniedForever) {
          setState(() => _loadingLocation = false);
          return;
        }
      }
      final position = await Geolocator.getCurrentPosition();
      setState(() {
        _latitude = position.latitude;
        _longitude = position.longitude;
      });

      final geocode =
          await ApiService.reverseGeocode(position.latitude, position.longitude);
      if (geocode.communeId != null) {
        setState(() => _communeId = geocode.communeId);
      }
      if (geocode.quartier != null) {
        _quartierController.text = geocode.quartier!;
      }
    } catch (_) {
      // Localisation facultative : l'utilisateur peut continuer sans.
    } finally {
      setState(() => _loadingLocation = false);
    }
  }

  Future<void> _pickFile(ImageSource source) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: source);
    if (picked != null) {
      setState(() => _file = File(picked.path));
    }
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _date ?? now,
      firstDate: DateTime(now.year - 2),
      lastDate: now,
    );
    if (picked != null) setState(() => _date = picked);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_file == null) {
      _showMessage('Veuillez joindre une photo ou une vidéo.');
      return;
    }
    if (_communeId == null) {
      _showMessage('Veuillez choisir une commune.');
      return;
    }
    if (_latitude == null || _longitude == null) {
      _showMessage('Position GPS indisponible, réessayez.');
      return;
    }

    setState(() => _submitting = true);
    try {
      final message = await ApiService.submitAlerte(
        communeId: _communeId!,
        quartier: _quartierController.text,
        risque: _risque!,
        description: _descriptionController.text,
        date: _date,
        latitude: _latitude!,
        longitude: _longitude!,
        file: _file!,
      );
      _showMessage(message);
      _resetForm();
    } catch (e) {
      _showMessage(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      setState(() => _submitting = false);
    }
  }

  void _resetForm() {
    _quartierController.clear();
    _descriptionController.clear();
    setState(() {
      _risque = null;
      _date = null;
      _file = null;
    });
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  void dispose() {
    _quartierController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Émettre une alerte')),
      body: _loadingCommunes
          ? const Center(child: CircularProgressIndicator())
          : _loadError != null
              ? Center(child: Text(_loadError!))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        DropdownButtonFormField<int>(
                          initialValue: _communeId,
                          decoration: const InputDecoration(labelText: 'Commune'),
                          items: _communes
                              .map((c) => DropdownMenuItem<int>(
                                    value: c['id'] as int,
                                    child: Text(c['commune'] as String),
                                  ))
                              .toList(),
                          onChanged: (v) => setState(() => _communeId = v),
                          validator: (v) => v == null ? 'Choisissez une commune' : null,
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: _quartierController,
                          decoration: const InputDecoration(labelText: 'Quartier'),
                        ),
                        const SizedBox(height: 12),
                        DropdownButtonFormField<String>(
                          initialValue: _risque,
                          decoration: const InputDecoration(labelText: 'Type de risque'),
                          items: risquesDisponibles
                              .map((r) => DropdownMenuItem(value: r, child: Text(r)))
                              .toList(),
                          onChanged: (v) => setState(() => _risque = v),
                          validator: (v) => v == null ? 'Choisissez un type de risque' : null,
                        ),
                        const SizedBox(height: 12),
                        ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text(_date == null
                              ? "Date de l'événement (optionnel)"
                              : '${_date!.day}/${_date!.month}/${_date!.year}'),
                          trailing: const Icon(Icons.calendar_today),
                          onTap: _pickDate,
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: _descriptionController,
                          decoration: const InputDecoration(labelText: 'Description'),
                          maxLines: 3,
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _pickFile(ImageSource.camera),
                                icon: const Icon(Icons.camera_alt),
                                label: const Text('Caméra'),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _pickFile(ImageSource.gallery),
                                icon: const Icon(Icons.photo_library),
                                label: const Text('Galerie'),
                              ),
                            ),
                          ],
                        ),
                        if (_file != null)
                          Padding(
                            padding: const EdgeInsets.only(top: 8),
                            child: Text('Fichier sélectionné : ${_file!.path.split('/').last}'),
                          ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Icon(Icons.my_location,
                                color: _latitude != null ? Colors.green : Colors.grey),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                _loadingLocation
                                    ? 'Localisation en cours…'
                                    : _latitude != null
                                        ? 'Position : ${_latitude!.toStringAsFixed(5)}, ${_longitude!.toStringAsFixed(5)}'
                                        : 'Position indisponible',
                              ),
                            ),
                            TextButton(
                              onPressed: _loadingLocation ? null : _captureLocation,
                              child: const Text('Actualiser'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        FilledButton(
                          onPressed: _submitting ? null : _submit,
                          child: _submitting
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('Envoyer'),
                        ),
                      ],
                    ),
                  ),
                ),
    );
  }
}
